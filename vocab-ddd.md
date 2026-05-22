# DDD 語彙

「これモデルに書いた方がいい」を **「{DDD 概念} の観点から {現状 X} を {提案 Y} にすると {改善される性質 Z} が改善される」** 表現に変換するための語彙
業務ロジックが濃いドメイン (注文・在庫・配送等) で費用対効果が高い


## TL;DR
- DDD は「**業務ロジックをコード上のどこに置くか・どう境界を引くか**」の判断軸を提供する  Entity / Value Object / Aggregate / Repository などのパターンは手段
- 戦術だけ持ち出して「これ Aggregate にする」「Value Object にする」と言うのはノイズ  必ず**業務的な意味**から議論
- 対象ドメインの**用語辞書 (業務語 ↔ コード上の名前)** を揃えるだけでも議論精度が上がる

## Contents
- 戦略的設計
	- Ubiquitous Language
	- Bounded Context
	- Context Map
	- Anti-Corruption Layer (ACL)
- 戦術的設計 (Building Blocks)
	- Entity
	- Value Object
	- Aggregate (Aggregate Root)
	- Domain Service
	- Application Service
	- Repository
	- Factory
	- Domain Event
- アンチパターン
	- Anemic Domain Model

---

## 戦略的設計

### Ubiquitous Language
- **読み**: ユビキタス ランゲージ
- **定義**: ユビキタス言語  業務側とエンジニアが同じ言葉でドメインを語る  コード上の名前と業務用語が一致する
- **使うとき**:
  - **Ubiquitous Language** の観点から、{コード上の `discount_type`} と業務語 (期間限定セール / 会員割引) のマッピングを揃えると、**業務側との会話精度と新規参入者のオンボーディング** が改善される。
- **適用すべきケース**:
  - 同じ概念に対し、業務側では「期間限定セール」、コードでは `campaign_discount`、画面では「タイムセール」のように呼称がバラバラに並立している
- **適用すべきでないケース**:
  - **業務側に明確な語彙がない**横断系機能 (ロギング / セッション管理 / 通知配信) で Ubiquitous Language を要求するのは過剰
  - 既存コードの大規模リネームで PR が肥大化、レビュー不能になる
- **トレードオフ**: 業務語整合 ↔ リネームコスト  → **新規機能から徐々に**揃える
- **関連**: Bounded Context / 命名

### Bounded Context
- **読み**: バウンデッド コンテキスト
- **定義**: 境界づけられたコンテキスト  1 つのモデル/語彙が一貫して通用する範囲  同じ語が文脈で意味を変えるなら別 Context
- **使うとき**:
  - **Bounded Context** の観点から、{注文 Context の Customer} と {請求 Context の Customer} を別型にすると、**Context 跨ぎのモデル変更時の波及範囲** が改善される。
- **適用すべきケース**:
  - 同じ語が複数文脈で違う意味
  - 巨大な共有 Entity が全機能から参照されてる
- **適用すべきでないケース**:
  - **小規模アプリ**で Context を細切れにすると、CRUD 1 個に大量の型変換が発生
  - **試作期**で Context 境界が固まる前に分割すると後悔
- **トレードオフ**: 隔離 ↔ 統合コスト  → 「**業務側の組織境界**」と一致しやすい
- **関連**: Context Map / ACL / Conway's Law

### Context Map
- **読み**: コンテキスト マップ
- **定義**: コンテキスト地図  複数の Bounded Context 同士の関係 (上下流・連携方法) を図にしたもの
- **使うとき**:
  - **Context Map** 上、{Context A} は {Context B} の下流なので、**ACL で防御** すべき。
- **適用すべきケース**:
  - 複数 Context があり連携が暗黙化してる
  - 変更時の影響範囲が誰にも分からない
- **適用すべきでないケース**:
  - 単一 Context の小規模アプリで Context Map を要求 (図が 1 ノードになって意味なし)
- **トレードオフ**: 可視性 ↔ メンテコスト  → 大変更時に必ず更新する運用が組めるかが鍵
- **関連**: Bounded Context / ACL

### Anti-Corruption Layer / ACL
- **読み**: アンチコラプション レイヤー / エーシーエル
- **定義**: 外部システム / レガシー DB の語彙やデータ形式を、自分のドメイン語彙に翻訳する隔離層  外部仕様の変更が Domain 層に直接届かないように分離する
- **使うとき**:
  - **ACL** の観点から、{外部 API (決済 API) のレスポンス JSON} を **内部モデルに翻訳する層** を挟むと、**外部仕様変更時の Domain 非侵襲性** が改善される。
- **適用すべきケース**:
  - 外部 API
  - レガシー DB
  - 別チーム所有システムとの連携
- **適用すべきでないケース**:
  - **自社内の安定 API** に ACL を入れて、ただの間接化増殖
  - 1:1 マッピングしかない単純連携
- **トレードオフ**: 隔離 ↔ マッピングコスト
- **コード例**:
  ```php
  // before: 外部 API の語彙 (謎の短縮フィールド名・コード) が業務ロジックに漏れる
  class CustomerImporter {
      public function import(): void {
          $resp = $this->externalApi->fetch();
          foreach ($resp['customers'] as $c) {
              $customer = new Customer();
              $customer->name = $c['nm'];      // 外部の謎フィールド名
              $customer->status = $c['st_cd']; // 外部の謎コード "A" / "I" / "S"
              $this->repo->save($customer);
          }
      }
  }

  // after: ACL が外部語彙を内部語彙に翻訳  業務ロジックは内部語彙だけ知ってればよい
  class ExternalCustomerAdapter {
      /** @return Customer[] */
      public function fetchAndAdapt(): array {
          $resp = $this->externalApi->fetch();
          return array_map(fn($c) => new Customer(
              name: $c['nm'],
              status: $this->translateStatus($c['st_cd']),
          ), $resp['customers']);
      }
      private function translateStatus(string $code): CustomerStatus {
          return match ($code) {
              'A' => CustomerStatus::Active,
              'I' => CustomerStatus::Inactive,
              'S' => CustomerStatus::Suspended,
          };
      }
  }
  class CustomerImporter {
      public function import(): void {
          foreach ($this->adapter->fetchAndAdapt() as $customer) {
              $this->repo->save($customer);
          }
      }
  }
  ```
- **関連**: Boundary / Adapter / Hexagonal

---

## 戦術的設計 (Building Blocks)

### Entity
- **読み**: エンティティ
- **定義**: エンティティ  ID で同一性が決まるオブジェクト  属性が変わっても「同じもの」
- **使うとき**:
  - **Entity** の観点から、属性比較で equals している現状を **ID 比較** にすると、**同一性判定の正しさ** が改善される。
- **適用すべきケース**: 業務的に「同じ◯◯」と呼べるが状態は変わるもの (注文・顧客・店舗)
- **適用すべきでないケース**:
  - **不変な値** (金額・電話番号・住所) を Entity 化 → これは Value Object であるべき
- **トレードオフ**: 同一性管理 ↔ 不変性のメリット放棄
- **コード例**:
  ```php
  // before: 属性比較で同一性を判定  住所変更で「別人」になってしまう
  function isSameCustomer(Customer $a, Customer $b): bool {
      return $a->name === $b->name && $a->address === $b->address;
  }

  // after: ID で同一性を判定  属性が変わっても「同じ顧客」
  final class CustomerId {
      public function __construct(public readonly string $value) {}
      public function equals(CustomerId $other): bool {
          return $this->value === $other->value;
      }
  }
  class Customer {
      public function __construct(
          public readonly CustomerId $id,
          public string $name,
          public string $address,
      ) {}
      public function equals(Customer $other): bool {
          return $this->id->equals($other->id);
      }
      public function changeAddress(string $newAddress): void {
          $this->address = $newAddress; // 属性が変わっても ID は不変
      }
  }
  ```
- **関連**: Value Object (対) / Aggregate

### Value Object
- **読み**: バリュー オブジェクト
- **定義**: 値オブジェクト  属性の値で同一性が決まる不変オブジェクト  ID を持たない
- **使うとき**:
  - **Value Object** の観点から、{金額} を string/int から **Money Value Object** にすると、**通貨単位混同の防止とバリデーション集約** が改善される。
- **適用すべきケース**: 金額・通貨・電話番号・メール・郵便番号・期間 等の業務概念
- **適用すべきでないケース**:
  - 汎用カウンタ・汎用ラベル等、業務概念が薄い数値/文字列
  - **既存コードの全プリミティブを一斉 Value Object 化** → PR 肥大化、レビュー不能
- **トレードオフ**: 型安全 ↔ 変換コスト  → 業務濃いドメイン (金額・通貨・期間等) で好相性
- **コード例**:
  ```php
  // before: 金額が int 単体  通貨単位を取り違える事故が起きる
  function transfer(int $amountJpy, int $amountUsd): void {
      // どちらが JPY か取り違えても型で防げない
  }

  // after: Money Value Object  金額と通貨が型で結びついて取り違え不可能
  enum Currency: string { case JPY = 'JPY'; case USD = 'USD'; }
  final class Money {
      public function __construct(
          public readonly int $amount,
          public readonly Currency $currency,
      ) {
          if ($amount < 0) throw new InvalidArgumentException('negative amount');
      }
      public function add(Money $other): Money {
          if ($this->currency !== $other->currency) {
              throw new InvalidArgumentException('currency mismatch');
          }
          return new Money($this->amount + $other->amount, $this->currency);
      }
      public function equals(Money $other): bool {
          return $this->amount === $other->amount && $this->currency === $other->currency;
      }
  }
  function transfer(Money $amount): void {
      // 通貨混同が型レベルで弾かれる
  }
  ```
- **関連**: Primitive Obsession / Replace Primitive with Object

### Aggregate / Aggregate Root
- **読み**: アグリゲイト / アグリゲイト ルート
- **定義**: 集約 / 集約ルート  一貫性境界  関連 Entity/Value Object の塊で、外部からは Root 1 つを経由してしか触れない
- **使うとき**:
  - **Aggregate Root** の観点から、子 Entity を Repository から直接書き換えている現状を **Root 経由のみ** にすると、**不変条件の保証とトランザクション境界の明確化** が改善される。
- **適用すべきケース**: 関連 Entity 群に業務的な不変条件 (例: 注文合計 = 各明細の合計)
- **適用すべきでないケース**:
  - **Aggregate を巨大化**させて全部 1 つに押し込む (ロック競合 / 性能劣化)
  - **CRUD だけの単純テーブル**に Aggregate 概念を持ち込んで肥大化
- **トレードオフ**: 一貫性 ↔ 性能 / 並行性  → Aggregate は**小さく保つ**が原則
- **コード例**:
  ```php
  // before: 子 (OrderLine) を子用 Repository から直接書き換え  Order の不変条件が壊れる
  class OrderLineRepository {
      public function save(OrderLine $line): void { /* ... */ }
  }
  $line = $lineRepo->find($lineId);
  $line->quantity = 999; // Order::$total と整合性が崩れる
  $lineRepo->save($line);

  // after: Aggregate Root (Order) 経由でしか触らない  不変条件は Root が保証
  class Order {
      /** @var OrderLine[] */
      private array $lines = [];
      private int $total = 0;

      public function changeLineQuantity(OrderLineId $lineId, int $newQuantity): void {
          $line = $this->findLine($lineId);
          $line->changeQuantity($newQuantity);
          $this->recalculateTotal(); // 不変条件 (total = 各 line の合計) を維持
      }
      private function recalculateTotal(): void {
          $this->total = array_sum(array_map(fn($l) => $l->subtotal(), $this->lines));
      }
  }
  // Repository は Aggregate 単位  子 Entity 用 Repository は作らない
  class OrderRepository {
      public function save(Order $order): void { /* Aggregate 全体を保存 */ }
  }
  ```
- **関連**: SRP / Tell Don't Ask / Repository / Domain Event

### Domain Service
- **読み**: ドメイン サービス
- **定義**: ドメインサービス  どの Entity/Value Object にも置き場が無いドメインロジックを置く  動詞的な振る舞い
- **使うとき**:
  - **Domain Service** の観点から、**Aggregate を跨ぐ調整ロジック** を Service に切り出すと、**Aggregate 肥大化と責務集約** が改善される。
- **適用すべきケース**:
  - 複数 Aggregate 跨ぎの調整
  - 1 Entity に置くと不自然な振る舞い
- **適用すべきでないケース**:
  - **何でも Service に書く** → Anemic Domain Model 化
  - **Application Service と混同**して UseCase 的シナリオを Domain Service に書く
- **トレードオフ**: 集約 ↔ 貧血化  → 「**Entity/Value Object に置けないか先に検討**」が鉄則
- **コード例**:
  ```php
  // before: Aggregate 跨ぎの調整 (送金) を Account 内に書いて責務が滲む
  class Account {
      public function transferTo(Account $other, Money $amount): void {
          // 自分以外の Aggregate (other) を内側から触っている
          $this->withdraw($amount);
          $other->deposit($amount);
      }
  }

  // after: Aggregate 跨ぎは Domain Service に切り出す  Account は自分の責務だけ
  class TransferService {
      public function transfer(Account $from, Account $to, Money $amount): void {
          $from->withdraw($amount);
          $to->deposit($amount);
      }
  }
  // Account は withdraw / deposit (自分自身への操作) だけに集中できる
  ```
- **関連**: Anemic Domain Model (アンチ) / SRP / Application Service

### Application Service / UseCase
- **読み**: アプリケーション サービス / ユースケース
- **定義**: アプリケーションサービス  ユースケースを 1 つ実行するための薄い調整役  ドメインロジックは持たない
- **使うとき**:
  - **Application Service** の観点から、業務ルール (「会員割引適用時は通常価格を非表示」) を **Domain (Entity/Value Object/Domain Service) に下ろす** と、**業務ルールの再利用性と Application Service の薄さ** が改善される。
- **適用すべきケース**:
  - トランザクション境界
  - 入出力境界
  - 外部システム連携の調整
- **適用すべきでないケース**:
  - Application Service が業務判断を抱えて肥大化 (= Anemic Domain Model の典型)
- **トレードオフ**: 薄さ ↔ Domain 側の表現力
- **コード例**:
  ```php
  // before: Application Service が業務ルール (割引計算) を抱えて肥大化
  class PlaceOrderService {
      public function execute(int $userId, array $items): void {
          $user = $this->users->find($userId);
          $subtotal = 0;
          foreach ($items as $i) $subtotal += $i->price * $i->qty;
          $discount = $user->rank === 'gold' ? (int) round($subtotal * 0.15) : 0;
          $total = $subtotal - $discount;
          $order = new Order(userId: $userId, total: $total);
          $this->orders->save($order);
      }
  }

  // after: 業務ルールを Domain に下ろし、Application Service は薄い調整役だけに
  class PlaceOrderService {
      public function execute(UserId $userId, array $items): void {
          $user = $this->users->find($userId);
          $order = Order::placeFor($user, $items); // 業務ルールは Order::placeFor 内
          $this->orders->save($order);
      }
  }
  ```
- **関連**: Use Case / SRP / Anemic Domain Model

### Repository
- **読み**: リポジトリ
- **定義**: リポジトリ  Aggregate の永続化入出力をコレクションのように見せる抽象  interface は Domain 側に置く
- **使うとき**:
  - **Repository** の観点から、UseCase が Eloquent に直接依存している現状を **Repository interface を Domain に + 実装を Infra に** すると、**テスト時のモック容易性と ORM 差し替え可能性** が改善される。
- **適用すべきケース**:
  - Aggregate 単位の永続化が必要
  - テストで DB を切りたい
- **適用すべきでないケース**:
  - **CRUD だけの単純テーブル**で Repository を要求 (ORM 直使いで十分なケース)
  - SQL を直接返す / DB スキーマがそのまま漏れる Repository (= 抽象が壊れてる)
- **トレードオフ**: 抽象化 ↔ ORM 機能の制約
- **コード例**:
  ```php
  // before: UseCase が Eloquent / SQL を直接叩く  テストで本物の DB が必要
  class CancelOrderUseCase {
      public function execute(int $orderId): void {
          $row = DB::table('orders')->find($orderId);
          DB::table('orders')->where('id', $orderId)->update(['status' => 'canceled']);
      }
  }

  // after: Repository interface を Domain に  実装は Infra 層に
  // Domain 層
  interface OrderRepository {
      public function find(OrderId $id): ?Order;
      public function save(Order $order): void;
  }
  // Infra 層
  class EloquentOrderRepository implements OrderRepository {
      public function find(OrderId $id): ?Order { /* ... */ }
      public function save(Order $order): void { /* ... */ }
  }
  // UseCase は Repository interface にだけ依存
  class CancelOrderUseCase {
      public function __construct(private OrderRepository $orders) {}
      public function execute(OrderId $orderId): void {
          $order = $this->orders->find($orderId);
          $order->cancel();
          $this->orders->save($order);
      }
  }
  ```
- **関連**: DIP / Aggregate / Hexagonal

### Factory
- **読み**: ファクトリ
- **定義**: ファクトリ  複雑な生成ロジックを集約  Aggregate の不変条件を満たした状態で組み立てる
- **使うとき**:
  - **Factory** の観点から、コンストラクタが肥大化している現状を Factory に切り出すと、**生成時の不変条件チェック集約** が改善される。
- **適用すべきケース**:
  - 不変条件チェックが複数ある
  - コンストラクタ引数が 5 個以上
- **適用すべきでないケース**:
  - 単純な値構築まで Factory 化 (ボイラープレート増殖)
- **トレードオフ**: 集約 ↔ クラス数
- **コード例**:
  ```php
  // before: コンストラクタが肥大化  生成ロジックと不変条件チェックが混在
  class Order {
      public function __construct(
          public CustomerId $customerId,
          public array $itemRequests,
          public ShippingMethod $shipping,
          public PaymentMethod $payment,
      ) {
          if (count($itemRequests) === 0) throw new InvalidArgumentException('empty');
          // 在庫チェック、SKU 検証、価格スナップショット取得 ...
          // OrderLine の組み立て ...
          // 合計計算 ...
      }
  }

  // after: 組み立て責務を Factory に集約  Order は最小限の不変条件だけ
  final class OrderFactory {
      public function __construct(
          private ProductRepository $products,
          private StockChecker $stock,
      ) {}
      public function create(
          CustomerId $customerId,
          array $itemRequests,
          ShippingMethod $shipping,
      ): Order {
          $items = $this->buildItems($itemRequests);
          $this->stock->ensureAvailable($items);
          return new Order(
              customerId: $customerId,
              items: $items,
              placedAt: new DateTimeImmutable(),
              shipping: $shipping,
          );
      }
      private function buildItems(array $requests): array { /* ... */ }
  }
  ```
- **関連**: Aggregate / Builder

### Domain Event
- **読み**: ドメイン イベント
- **定義**: ドメインイベント  ドメイン内で起きた重要な出来事を表すオブジェクト  時制は過去形 (`ReservationConfirmed`)
- **使うとき**:
  - **Domain Event** の観点から、**業務処理の中で直接メール送信している現状** を **Event 発行 + Listener** にすると、**業務ロジックと副作用の疎結合性** が改善される。
- **適用すべきケース**:
  - 業務イベントが複数の副作用 (通知/集計/外部連携) を引き起こす
  - 副作用の追加が頻繁
- **適用すべきでないケース**:
  - **副作用が 1 個しかない**箇所で Event 化 → ただの間接化
  - **同期で確実に実行されないと困る処理** を非同期 Event 化して整合性を壊す
- **トレードオフ**: 疎結合 ↔ デバッグ難易度  → Event の流れが追えなくなりがちなので可観測性が前提
- **コード例**:
  ```php
  // before: 業務処理の中で直接メール送信  副作用追加で業務ロジックが汚染される
  class Order {
      public function confirm(Mailer $mailer): void {
          $this->status = 'confirmed';
          $mailer->send($this->customerEmail, 'Confirmed'); // ← 業務ロジックに直書き
      }
  }

  // after: Domain Event を発行  Listener が副作用を担当  業務ロジックは純粋
  final class OrderConfirmed {
      public function __construct(
          public readonly OrderId $orderId,
          public readonly Email $customerEmail,
          public readonly DateTimeImmutable $occurredAt,
      ) {}
  }
  class Order {
      /** @var object[] */
      private array $events = [];
      public function confirm(): void {
          $this->status = 'confirmed';
          $this->events[] = new OrderConfirmed(
              $this->id, $this->customerEmail, new DateTimeImmutable()
          );
      }
      public function pullEvents(): array {
          $events = $this->events;
          $this->events = [];
          return $events;
      }
  }
  // OrderConfirmed → メール送信 / 集計 / 外部連携 を個別の Listener が担当
  ```
- **関連**: 副作用の局所化 / 結合度 / Eventual Consistency

---

## アンチパターン

### Anemic Domain Model
- **読み**: アニミック ドメイン モデル
- **何が起きてる**: Entity がただのデータ入れ物 (getter/setter のみ)、業務ロジックが全部 Service に集まる
- **使うとき**:
  - **Anemic Domain Model** になりかけている。業務ルールを Entity 側に下ろすと、**ドメイン表現力と Tell Don't Ask 適合度** が改善される。
- **なぜまずい**: SRP 違反 + Tell Don't Ask 違反のダブルコンボ  Entity が「何ができるか」を語らない
- **適用すべきでない反論**: **意図的に Anemic にする選択肢もある** (シンプルな CRUD アプリで DDD を採用しないケース)  → 「業務ロジック濃いドメインなら Anemic は避ける」と前提を揃えてから議論
- **コード例**:
  ```php
  // before: Entity がデータ入れ物  業務ルールが全部 Service に流出 (Anemic)
  class Account {
      public int $balance;
      public bool $frozen;
  }
  class AccountService {
      public function withdraw(Account $account, int $amount): void {
          if ($account->frozen) throw new RuntimeException('account is frozen');
          if ($account->balance < $amount) throw new RuntimeException('insufficient funds');
          $account->balance -= $amount;
      }
  }

  // after: Entity が業務ルールを持つ (リッチドメイン)  Service は薄くなる
  class Account {
      public function __construct(
          private int $balance,
          private bool $frozen,
      ) {}
      public function withdraw(int $amount): void {
          if ($this->frozen) throw new RuntimeException('account is frozen');
          if ($this->balance < $amount) throw new RuntimeException('insufficient funds');
          $this->balance -= $amount;
      }
      public function balance(): int { return $this->balance; }
  }
  // 呼び出し側: $account->withdraw($amount);
  ```
- **関連**: Domain Service / Feature Envy
