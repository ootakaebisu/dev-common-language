# アーキテクチャ語彙

「{レイヤー} に書いた方が良くない？」を **「{アーキテクチャ概念} の観点から {現状の依存/責務 X} を {提案 Y} にすると {改善される性質 Z} が改善される」** 表現に変換するための語彙
ViewModel / UseCase / Repository 構成の議論で前提を揃える


## TL;DR
- アーキテクチャ議論で空中戦になったら、**現コードベースのレイヤ構成と依存方向**に戻る  「あるべき」より「ある」から議論
- 採用済みアーキテクチャ (例: Layered + Hexagonal 折衷) の前提を冒頭で確認すると揃いやすい
- 依存方向の議論は **インポート文を 1 枚見る** だけで具体化する  抽象論より現物

## Contents
- アーキテクチャパターン
	- Layered Architecture
	- Clean Architecture
	- Hexagonal Architecture (Ports & Adapters)
- 構造概念
	- 依存方向
	- レイヤー責務
	- Boundary
	- Use Case
	- Presenter (ViewModel) 
	- DTO

---

## アーキテクチャパターン

### Layered Architecture
- **読み**: レイヤード アーキテクチャ
- **定義**: Presentation / Application / Domain / Infrastructure の縦積み  上から下への一方向依存
- **使うとき**:
  - **レイヤー責務** の観点から、Controller が DB に直接アクセスしている現状を **UseCase / Repository 経由** にすると、**業務ロジックのテスト容易性と再利用性** が改善される。
- **適用すべきケース**:
  - 中規模 Web アプリ
  - CRUD 中心
  - チーム規模が中程度
- **適用すべきでないケース**:
  - **マイクロサービス境界をまたぐ議論**にレイヤ語彙を持ち込む (別語彙が必要)
  - **CLI ツール / バッチスクリプト**等、Presentation が薄い文脈
- **トレードオフ**: 構造の明快さ ↔ 過剰な抽象化  → 規模に応じてレイヤ数を調整
- **関連**: 依存方向 / DIP / Clean Architecture

### Clean Architecture
- **読み**: クリーン アーキテクチャ
- **定義**: 同心円 (Entities / UseCases / Interface Adapters / Frameworks) で依存は必ず内側に向く  外側の変更が中心 (業務ロジック) に伝播しない
- **使うとき**:
  - **Clean Architecture の依存規則** の観点から、UseCase が Eloquent を直接 new している現状を **interface を内側に置いて Adapter で実装** すると、**ORM 移行コストとユニットテストの実行コスト** が改善される。
- **適用すべきケース**:
  - 長期保守想定
  - フレームワークの寿命より業務ロジック寿命が長い
  - 複数のインターフェース (Web/CLI/Batch) を想定
- **適用すべきでないケース**:
  - **MVP/プロトタイプ**で同心円全部切ると過剰  まず Layered で十分
  - **CRUD アプリで業務ロジックがほぼない**箇所まで適用 (ボイラープレート増加だけが残る)
- **トレードオフ**: 移行性/テスト容易性 ↔ 初期実装コスト + 認知負荷
- **関連**: DIP / Hexagonal / Boundary

### Hexagonal Architecture (Ports & Adapters)
- **読み**: ヘキサゴナル アーキテクチャ / ポーツ アンド アダプターズ
- **定義**: 業務ロジック (Hexagon 内側) と外界を Port (interface) と Adapter (実装) で分離  内側はテストで差し替え可能
- **使うとき**:
  - **Ports & Adapters** の観点から、外部 API (決済 API) を **Port 定義 + Adapter 実装** に分離すると、**外部 API 変更時の影響範囲と統合テストのモック容易性** が改善される。
- **適用すべきケース**:
  - 外部システム連携が複数
  - テストで外部依存を切りたい
  - 同じドメインロジックを複数のインターフェースで公開
- **適用すべきでないケース**:
  - 外部依存が 1 個もない純粋計算ライブラリ
  - 短命のスクリプト
- **トレードオフ**: 隔離性 ↔ 間接化の認知負荷
- **関連**: DIP / ACL / Clean Architecture

---

## 構造概念

### 依存方向
- **定義**: 「誰が誰を知っているか」の矢印の向き  業務ロジックが外側 (フレームワーク/DB/UI) を知ってはいけない (= Dependency Direction)
- **使うとき**:
  - **依存方向** の観点から、{Domain} → {Infra} の矢印を **interface 経由で逆転** すると、**DB 変更時の Domain 非侵襲性** が改善される。
- **適用すべきケース**:
  - Domain から Infra への直接参照
  - Domain Entity が ORM 親クラスを継承
  - フレームワークのアノテーション漏れ
- **適用すべきでないケース**:
  - **小規模ツール**で逆転を完全適用すると、interface だけ増えて利益が薄い
  - **Active Record パターンを意図的に採用してる箇所** (Rails 系) で「依存方向が…」と言うと議論が空中戦化  → まず現コードベースで採用しているアーキテクチャパターンを確認
- **トレードオフ**: 純度 ↔ ボイラープレート  → 「**変更頻度が高い境界**」に絞って逆転
- **コード例**:
  ```php
  // before: Domain Entity が ORM (Infra) を直接継承  矢印が外向きに伸びている
  namespace App\Models;
  class Order extends \Illuminate\Database\Eloquent\Model {
      protected $table = 'orders';
      public function customer() { return $this->belongsTo(Customer::class); }
  }
  // Domain が Infra (ORM) を知っている = 依存方向が逆

  // after: Domain は ORM を知らない  Repository interface を介して Infra が Domain を知る
  namespace App\Domain;
  class Order {
      public function __construct(
          public readonly OrderId $id,
          public readonly CustomerId $customerId,
          private OrderStatus $status,
      ) {}
      public function cancel(): void { $this->status = OrderStatus::Canceled; }
  }
  interface OrderRepository {
      public function find(OrderId $id): ?Order;
      public function save(Order $order): void;
  }

  namespace App\Infra;
  // Infra → Domain への依存 (矢印が内向きに反転した)
  class EloquentOrderRepository implements \App\Domain\OrderRepository { /* ... */ }
  ```
- **関連**: DIP / Boundary

### レイヤー責務
- **定義**: 各レイヤが何を持って良くて何を持ってはいけないかの境界  越境すると凝集度が壊れる (= Layer Responsibility)
- **使うとき**:
  - **レイヤー責務** の観点から、{Controller で業務判断している現状} を **UseCase に上げる** と、**業務ロジックの再利用性とテスト容易性** が改善される。
- **適用すべきケース**:
  - Controller でバリデーション+業務判断+永続化を全部書いてる
  - UseCase に HTTP ステータスや JSON 整形が混ざってる
- **適用すべきでないケース**:
  - 単純な **CRUD エンドポイント**で UseCase を必須化 (Controller 直書きで十分なケース)
  - レイヤ責務の議論を**全機能に一律適用**しようとする (機能の複雑度で粒度を変えるべき)
- **トレードオフ**: 一貫性 ↔ 複雑度に応じた柔軟性
- **コード例**:
  ```php
  // before: Controller がバリデーション + 業務判断 + 永続化 + 整形を全部抱える
  class OrderController {
      public function place(Request $req): Response {
          $items = $req->input('items');
          if (count($items) === 0) abort(400, 'empty');

          $subtotal = 0;
          foreach ($items as $i) $subtotal += $i['price'] * $i['qty'];
          $tax = (int) round($subtotal * 0.1);
          $total = $subtotal + $tax;

          DB::table('orders')->insert(['total' => $total]);
          return new Response(['total' => $total]);
      }
  }

  // after: Controller は HTTP 境界のみ、UseCase が業務シナリオ、Repository が永続化
  class OrderController {
      public function __construct(private PlaceOrderUseCase $useCase) {}
      public function place(Request $req): Response {
          $output = $this->useCase->execute(new PlaceOrderInput($req->input('items')));
          return new Response(['total' => $output->total]);
      }
  }
  class PlaceOrderUseCase {
      public function __construct(private OrderRepository $orders) {}
      public function execute(PlaceOrderInput $input): PlaceOrderOutput {
          // 業務判断はここに集約
      }
  }
  ```
- **関連**: SRP / Clean Architecture

### Boundary
- **読み**: バウンダリー
- **定義**: 境界  依存の向きを切り替える線  interface / DTO / イベントが境界を定義する
- **使うとき**:
  - **境界 (Boundary)** の観点から、**DB Entity を UI 層まで貫通させている現状** を **DTO で受け渡す** にすると、**スキーマ変更時の UI への影響範囲** が改善される。
- **適用すべきケース**:
  - DB スキーマ由来の Entity が API レスポンスまで流れてる
  - 外部 API のレスポンス JSON が Domain まで流れてる
- **適用すべきでないケース**:
  - **完全な内部ツール** で境界変換を 3 段挟む (オーバーエンジニアリング)
  - 1:1 マッピングしかない箇所で DTO を増やす (ただの増殖)
- **トレードオフ**: 隔離性 ↔ マッピングコスト  → 「**外部に晒す境界 / 変更頻度の高い境界**」に集中
- **コード例**:
  ```php
  // before: DB Entity が API レスポンスまで貫通  カラム名 (internal_status_code 等) が漏れる
  class OrderApiController {
      public function show(int $id): JsonResponse {
          $order = OrderModel::find($id); // ORM Entity
          return response()->json($order); // 内部カラム名がそのまま外部に流出
      }
  }

  // after: DTO で境界を切る  内部スキーマと外部レスポンスを分離
  final class OrderResponse {
      public function __construct(
          public readonly string $orderId,
          public readonly int $totalAmount,
          public readonly string $status,
      ) {}
  }
  class OrderApiController {
      public function show(int $id): JsonResponse {
          $order = $this->orders->find(new OrderId($id));
          return response()->json(new OrderResponse(
              orderId: $order->id->value,
              totalAmount: $order->total(),
              status: $order->status->value,
          ));
          // DB カラム名を変えても外部インターフェースは壊れない
      }
  }
  ```
- **関連**: DIP / DTO / ACL

### Use Case
- **読み**: ユース ケース
- **定義**: 「ユーザーが何をしたいか」を 1 つのクラス/メソッドに閉じ込めたシナリオ単位
- **使うとき**:
  - これは **UseCase** に切り出すべき。**Controller でシナリオ組み立てしている現状** を UseCase に集約すると **同じシナリオの再利用と入力境界の明確化** が改善される。
- **適用すべきケース**:
  - Controller が複数 Repository を組み合わせて業務シナリオを書いてる
  - 同じシナリオが Web/CLI/Batch から呼ばれる
- **適用すべきでないケース**:
  - 単純な 1 Repository 呼び出しだけのエンドポイント
  - シナリオを**さらに別の UseCase を呼ぶ UseCase** で連鎖させる (UseCase 連鎖は debug 困難)
- **トレードオフ**: 集約 ↔ クラス数の増加
- **コード例**:
  ```php
  // before: Controller がシナリオを組み立て  CLI / Batch から再利用できない
  class OrderController {
      public function place(Request $req): Response {
          $customer = Customer::find($req->user()->id);
          $items = $this->buildItems($req->input('items'));
          $order = new Order($customer, $items);
          DB::transaction(function () use ($order) {
              $order->save();
              $this->mailer->send($order->customer->email, 'Confirmed');
          });
          return response()->json(['id' => $order->id]);
      }
  }

  // after: シナリオを UseCase に閉じ込める  Controller / CLI / Batch から共通で呼べる
  final class PlaceOrderUseCase {
      public function __construct(
          private CustomerRepository $customers,
          private OrderRepository $orders,
          private Mailer $mailer,
      ) {}
      public function execute(PlaceOrderInput $input): OrderId {
          return DB::transaction(function () use ($input) {
              $customer = $this->customers->find($input->customerId);
              $order = new Order($customer, $input->items);
              $this->orders->save($order);
              $this->mailer->send($customer->email, 'Confirmed');
              return $order->id;
          });
      }
  }
  class OrderController {
      public function place(Request $req): Response {
          $id = $this->useCase->execute(new PlaceOrderInput(/* ... */));
          return response()->json(['id' => $id->value]);
      }
  }
  ```
- **関連**: Application Service / SRP

### Presenter / ViewModel
- **読み**: プレゼンター / ビューモデル
- **定義**: UseCase の出力データを UI に渡す形に整える層
- **使うとき**:
  - **Presenter / ViewModel** の観点から、UseCase で `¥` 記号付き整形している現状を Presenter に移すと、**UseCase の表示形式独立性とロケール対応** が改善される。
- **適用すべきケース**:
  - UseCase 内で表示文字列・通貨記号・日時フォーマットを作ってる
  - 同じ UseCase の出力を複数 UI で違う形式で表示したい
- **適用すべきでないケース**:
  - 単一画面しかない管理画面で Presenter を必須化
  - 翻訳・整形ロジックが極めて単純な箇所
- **トレードオフ**: 関心分離 ↔ クラス増殖
- **コード例**:
  ```php
  // before: UseCase が表示用整形 (通貨記号 / 和暦) を内部で行う  Locale / UI 変更に弱い
  class GetOrderSummaryUseCase {
      public function execute(OrderId $id): array {
          $order = $this->orders->find($id);
          return [
              'total'    => '¥' . number_format($order->total),
              'placedAt' => $order->placedAt->format('Y年m月d日'),
          ];
      }
  }

  // after: UseCase は生の値を返し、Presenter が表示形式を担当
  final class OrderSummary {
      public function __construct(
          public readonly OrderId $orderId,
          public readonly Money $total,
          public readonly DateTimeImmutable $placedAt,
      ) {}
  }
  class GetOrderSummaryUseCase {
      public function execute(OrderId $id): OrderSummary { /* ... */ }
  }
  class JaJpOrderPresenter {
      public function present(OrderSummary $summary): array {
          return [
              'total'    => '¥' . number_format($summary->total->amount),
              'placedAt' => $summary->placedAt->format('Y年m月d日'),
          ];
      }
  }
  // 英語表示が必要になったら EnUsOrderPresenter を追加するだけで UseCase は無傷
  ```
- **関連**: SRP / Clean Architecture

### DTO
- **読み**: ディーティーオー (Data Transfer Object)
- **定義**: レイヤ間/プロセス間でデータだけを運ぶオブジェクト  振る舞いを持たない
- **使うとき**:
  - **DTO** で境界を切ると、{ORM Entity} の構造変更が UI に波及しなくなるので、**スキーマ変更の安全性** が改善される。
- **適用すべきケース**:
  - API レスポンス
  - 外部システム連携
  - プロセス間通信
- **適用すべきでないケース**:
  - **同一プロセス内・同一レイヤ内**で意味のない詰め替えを増やす
- **トレードオフ**: 隔離 ↔ 変換コスト
- **コード例**:
  ```php
  // before: ORM Entity を API レスポンスにそのまま使う  カラム変更で外部インターフェースが壊れる
  class OrderApiController {
      public function show(int $id): JsonResponse {
          $order = OrderModel::find($id);
          return response()->json($order); // internal_status_code / created_at_jst まで漏れる
      }
  }

  // after: API 専用の DTO に詰め替える  内部スキーマ変更が外部に波及しない
  final class OrderDto {
      public function __construct(
          public readonly string $id,
          public readonly int $total,
          public readonly string $status,
      ) {}
  }
  class OrderApiController {
      public function show(int $id): JsonResponse {
          $order = $this->orders->find(new OrderId($id));
          return response()->json(new OrderDto(
              id: $order->id->value,
              total: $order->total(),
              status: $order->status->value,
          ));
      }
  }
  ```
- **関連**: Boundary / Anti-Corruption Layer
