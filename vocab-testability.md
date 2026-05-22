# テスト容易性・依存設計

「テスト書きづらい」を **「{依存設計の概念} の観点から {現状の依存 X} を {提案 Y} にすると {テストの観点 Z} が改善される」** 表現に変換するための語彙
テストカバレッジ向上と直結する設計議論カテゴリ


## TL;DR
- 「テストが書きにくい」は **設計の悪臭** のシグナル  カバレッジが上がらないのは設計問題、根性で書く問題ではない
- Test Double を厳密に使い分けるだけで、テストの**実装依存度**が大きく下がる
- Mock を過度に使うとリファクタで壊れる  「**値で検証できることは値で**、振る舞いで検証すべきものだけ Mock」

## 出典書籍
- Working Effectively with Legacy Code (Michael Feathers) — Seam の原典
- xUnit Test Patterns (Gerard Meszaros) — Test Double 分類
- Growing Object-Oriented Software, Guided by Tests (Freeman & Pryce) — GOOS 本

---
## Contents
- 依存設計
	- Testability
	- Seam
	- Dependency Injection (DI)
	- Hard-coded Dependency
- Test Double (xUnit Test Patterns)
	- Dummy
	- Fake
	- Stub
	- Mock
	- Spy
- テストの設計概念
	- Pure Function
	- Test Pyramid
	- Test Ice Cream Cone
	- AAA (Given-When-Then)
	- Sociable Test vs Solitary Test
	- Characterization Test
	- 副作用境界

---

## 依存設計

### Testability
- **読み**: テスタビリティ
- **定義**: テスト容易性  コードの「テストの書きやすさ・速さ・安定性」を決める設計属性
- **使うとき**:
  - **Testability** の観点から、{現状の static method 直呼び出し} を **interface + DI** にすると、**ユニットテストの隔離性と実行速度** が改善される。
- **適用すべきケース**:
  - 既存コードのテストが書けない
  - テストが遅い
  - 並行実行で flaky
- **適用すべきでないケース**:
  - **設定値の取得**程度の依存にまで DI を要求 (過剰)
  - **試作期で要件が固まる前**にテスト容易性を理由に大幅リファクタ
- **トレードオフ**: テスト容易性 ↔ 実装コスト + 認知負荷
- **関連**: DIP / Seam / Hard-coded Dependency

### Seam
- **読み**: シーム
- **定義**: 継ぎ目  コードの **振る舞いを差し替えられる箇所** (Feathers の概念)  ここでテスト時のフェイクに切り替える
- **使うとき**:
  - **Seam** の観点から、{現状直接呼んでる箇所} に **Seam を入れて** テスト時の差し替えを可能にすると、**外部依存を切ったユニットテスト** が書けるようになる。
- **適用すべきケース**:
  - レガシーコードにテストを後付けする時
  - 外部 I/O が散在してる時
- **適用すべきでないケース**:
  - **全ての関数呼び出しに Seam を切る**と過剰、ただの間接化増殖
  - 既にテスト可能な箇所に新たに Seam を増設
- **トレードオフ**: 差し替え可能性 ↔ 直接呼び出しの単純さ
- **コード例**:
  ```php
  // before: time() を直接呼んでいるためテストで時刻を固定できない
  class OrderProcessor {
      public function place(Order $order): void {
          if (time() < $order->reserveOpenAt) {
              throw new RuntimeException('not yet open');
          }
          // ...
      }
  }

  // after: Clock interface を Seam として導入  テスト時は FakeClock に差し替え
  interface Clock {
      public function now(): int;
  }
  class OrderProcessor {
      public function __construct(private Clock $clock) {}
      public function place(Order $order): void {
          if ($this->clock->now() < $order->reserveOpenAt) {
              throw new RuntimeException('not yet open');
          }
      }
  }
  ```
- **関連**: DIP / Working Effectively with Legacy Code

### Dependency Injection / DI
- **読み**: ディペンデンシー インジェクション / ディーアイ
- **定義**: 依存性注入  依存オブジェクトを内部で new せず、外部から (constructor / setter / method) 注入する
- **使うとき**:
  - **Constructor Injection** で {Mailer} を注入すると、**テスト時に Fake Mailer に差し替えられて、ユニットテストの隔離性** が改善される。
- **適用すべきケース**:
  - 外部 I/O 依存
  - テストで切りたい依存
  - 実装が複数存在する依存
- **適用すべきでないケース**:
  - **データ構造的な依存** (config 値、定数) まで DI コンテナで管理 (過剰)
  - **DI コンテナの設定肥大化** で何がどこから注入されてるか不明になる
- **トレードオフ**: 注入の柔軟性 ↔ 設定/配線の複雑度
- **コード例**:
  ```php
  // before: クラス内で new  差し替え不可
  class OrderService {
      public function notify(Order $order): void {
          $mailer = new SmtpMailer();
          $mailer->send($order->customerEmail, 'Confirmed');
      }
  }

  // after: Constructor Injection で依存を注入
  class OrderService {
      public function __construct(private Mailer $mailer) {}
      public function notify(Order $order): void {
          $this->mailer->send($order->customerEmail, 'Confirmed');
      }
  }
  // 本番: new OrderService(new SmtpMailer());
  // テスト: new OrderService(new FakeMailer());
  ```
- **関連**: DIP / Inversion of Control

### Hard-coded Dependency
- **読み**: ハードコーデッド ディペンデンシー
- **定義**: クラス内で具象クラスを直接 new / static method を直接呼んでいる依存  テストで差し替えられない
- **使うとき**:
  - ここは **Hard-coded Dependency** になっていて、テスト時に切り離せない。**interface 化 + DI** にすると、**ユニットテストの実行可能性** が改善される。
- **適用すべきケース**:
  - クラス内で `new Mailer()` / `DB::table(...)` / `time()` のように、具象クラスの直接 new や static / グローバル関数の直接呼び出しが見つかる
- **適用すべきでないケース**:
  - **不変な内部ヘルパー**の new まで「Hard-coded だ」と指摘 (Value Object 等)
- **トレードオフ**: 単純さ ↔ テスト容易性
- **コード例**:
  ```php
  // before: クラス内で具象クラスを直接 new / static 呼び出し
  class ReportService {
      public function generate(): string {
          $today = date('Y-m-d');                            // ← 時刻も Hard-coded
          $orders = DB::table('orders')->whereDate('date', $today)->get(); // ← DB も Hard-coded
          return json_encode($orders);
      }
  }

  // after: Clock と OrderRepository を Inject  テスト時に Fake で差し替え可能
  class ReportService {
      public function __construct(
          private Clock $clock,
          private OrderRepository $orders,
      ) {}
      public function generate(): string {
          $orders = $this->orders->findByDate($this->clock->today());
          return json_encode($orders);
      }
  }
  ```
- **関連**: Seam / DI

---

## Test Double 分類 (xUnit Test Patterns)

### Dummy
- **読み**: ダミー
- **定義**: 引数として渡すが実際には使われないオブジェクト  null 代わりに型を満たすため
- **使うとき**:
  - ここは引数を埋めるだけなので **Dummy** で十分。
- **適用例**: テストで参照されない引数の Logger / Config
- **コード例**:
  ```php
  // OrderProcessor::place は logger を受け取るが、対象のテストケースでは呼ばれない
  class OrderProcessor {
      public function __construct(private Logger $logger, private Mailer $mailer) {}
      public function quickQuote(Order $order): int { return $order->subtotal; }
  }

  // test: 使われない依存を Dummy で埋める  型を満たすためだけの no-op 実装
  $dummyLogger = new class implements Logger { public function log(string $m): void {} };
  $dummyMailer = new class implements Mailer { public function send(string $t, string $s): void {} };
  $processor = new OrderProcessor($dummyLogger, $dummyMailer);
  self::assertSame(1000, $processor->quickQuote(new Order(subtotal: 1000)));
  ```

### Fake
- **読み**: フェイク
- **定義**: 実装は持つが本番用ではない簡易実装 (InMemoryRepository 等)
- **使うとき**:
  - DB 接続を切るため **Fake Repository (InMemory 実装)** を使うと、**テスト速度** が改善される。
- **適用例**: InMemoryRepository / InMemoryQueue / FakeMailer
- **コード例**:
  ```php
  // 本番では DB を使う Repository を、テストでは In-Memory 実装に差し替える
  interface OrderRepository {
      public function save(Order $order): void;
      public function find(int $id): ?Order;
  }
  final class InMemoryOrderRepository implements OrderRepository {
      /** @var array<int, Order> */
      private array $store = [];
      public function save(Order $order): void { $this->store[$order->id] = $order; }
      public function find(int $id): ?Order { return $this->store[$id] ?? null; }
  }
  // test:
  $repo = new InMemoryOrderRepository();
  $service = new OrderService($repo);
  $service->place($order);
  self::assertSame($order, $repo->find($order->id));
  ```

### Stub
- **読み**: スタブ
- **定義**: 呼ばれたら **決まった値を返す** 受動的な代替
- **使うとき**:
  - 外部 API レスポンスを **Stub** で固定すると、**テストの再現性** が改善される。
- **適用例**: 外部 API の固定レスポンス / 時刻取得の固定値
- **コード例**:
  ```php
  // 外部為替 API レスポンスを固定値で返す  本物の API は叩かない
  interface ExchangeRateApi {
      public function rate(string $from, string $to): float;
  }
  final class StubExchangeRateApi implements ExchangeRateApi {
      public function rate(string $from, string $to): float {
          return 150.0; // ← 固定値
      }
  }
  // test:
  $api = new StubExchangeRateApi();
  $service = new PricingService($api);
  self::assertSame(15000, $service->convertJpyFromUsd(100));
  ```

### Mock
- **読み**: モック
- **定義**: 「**特定の呼ばれ方をした**」ことを検証する  事前期待を仕込む
- **使うとき**:
  - {メソッド} が **正しい引数で呼ばれたこと** を検証したいので **Mock** で受ける。
- **適用例**: メール送信されたこと / イベント発行されたこと の検証
- **適用すべきでないケース**:
  - **値の検証で済む**ところに Mock を使って **実装に過度に依存**したテストにする → リファクタで壊れる
  - Mock を 3 段重ねて意味不明なテスト
- **コード例**:
  ```php
  // メール送信が「正しい引数で 1 回呼ばれた」ことを検証する (PHPUnit)
  public function testPlaceOrderSendsConfirmation(): void {
      $mailer = $this->createMock(Mailer::class);
      $mailer->expects(self::once())
          ->method('send')
          ->with('alice@example.com', 'Order Confirmed');

      $service = new OrderService($mailer);
      $service->place(new Order(customerEmail: 'alice@example.com', /* ... */));
      // 期待した引数で 1 回呼ばれなかったらテスト失敗
  }
  ```

### Spy
- **読み**: スパイ
- **定義**: 呼ばれた事実 (回数・引数) を **記録だけ** する  検証は後段で行う
- **使うとき**:
  - 呼ばれたかどうかだけ知りたいので **Spy** で十分。Mock のような事前期待は不要。
- **適用例**: ロガー呼び出しの記録 / イベント発行の記録
- **コード例**:
  ```php
  // 呼ばれた事実を内部に記録するだけ  事前期待は仕込まない
  final class SpyMailer implements Mailer {
      /** @var array<int, array{to: string, subject: string}> */
      public array $sent = [];
      public function send(string $to, string $subject): void {
          $this->sent[] = ['to' => $to, 'subject' => $subject];
      }
  }
  // test:
  $mailer = new SpyMailer();
  $service = new OrderService($mailer);
  $service->place($order);

  self::assertCount(1, $mailer->sent);
  self::assertSame('alice@example.com', $mailer->sent[0]['to']);
  ```

---

## テストの設計概念

### Pure Function
- **読み**: ピュア ファンクション
- **定義**: 純粋関数  同じ入力で同じ出力を返し、副作用を持たない関数  テストが最も書きやすい形
- **使うとき**:
  - {計算ロジック} を **Pure Function に切り出す** と、**ユニットテストの記述量と実行速度** が改善される。
- **コード例**:
  ```php
  // before: 計算ロジックに DB 依存  テストには DB 準備が必要
  class PricingService {
      public function discountedPrice(int $userId): int {
          $user = DB::table('users')->find($userId);
          $price = DB::table('products')->find($user->favProductId)->price;
          return (int) round($price * ($user->rank === 'gold' ? 0.85 : 1.0));
      }
  }

  // after: 計算ロジックを Pure Function に切り出す  テストは入力 → 出力だけ
  function applyDiscount(int $price, string $rank): int {
      return (int) round($price * ($rank === 'gold' ? 0.85 : 1.0));
  }
  // test (DB 不要):
  self::assertSame(850, applyDiscount(1000, 'gold'));
  self::assertSame(1000, applyDiscount(1000, 'silver'));
  ```
- **関連**: 副作用の局所化 / 関数型プログラミング

### Test Pyramid
- **読み**: テスト ピラミッド
- **定義**: テストピラミッド  Unit (多) > Integration (中) > E2E (少) の比率  下層ほど多く、速く、安定
- **使うとき**:
  - **Test Pyramid** の観点から、現状 E2E に偏ってる構成を **Unit 比率を上げる** と、**CI 実行時間と flaky テスト率** が改善される。
- **適用すべきでないケース**:
  - **業務クリティカルな境界**を Unit だけでカバーしようとする → 統合の罠を見逃す
  - 比率だけ追って、**意味のない Unit テスト**を量産

### Test Ice Cream Cone
- **読み**: テスト アイスクリーム コーン
- **定義**: ピラミッドの逆さま  E2E (多) > Integration > Unit (少)  遅く、flaky で、メンテ困難
- **使うとき**:
  - 現状 **Test Ice Cream Cone** になっている。**Unit テスト追加と E2E 削減** で実行時間が改善される。

### AAA / Given-When-Then
- **読み**: トリプルエー (Arrange-Act-Assert) / ギブン ウェン ゼン (Given-When-Then)
- **定義**: テストの構造化  準備 → 実行 → 検証 を明示的に分ける
- **使うとき**:
  - **AAA** で構造化すると、**テストの読みやすさとレビュー速度** が改善される。
- **コード例**:
  ```php
  // AAA で構造化したテスト  3 セクションが空行とコメントで明確に分かれる
  public function testWithdrawDeductsBalance(): void {
      // Arrange: テスト対象と入力を準備
      $account = new Account(balance: 1000, frozen: false);

      // Act: 1 つの操作を実行 (主役)
      $account->withdraw(300);

      // Assert: 期待値を検証
      self::assertSame(700, $account->balance());
  }
  ```

### Sociable Test vs Solitary Test
- **読み**: ソーシャブル テスト / ソリタリー テスト
- **定義**: Sociable = 依存先を実物使う / Solitary = 依存先を全部 Test Double にする
- **使うとき**:
  - ここは **Solitary** で書くと、依存先の挙動から切り離されて **テストの局所性** が改善される。
- **トレードオフ**: Solitary (隔離性) ↔ Sociable (結合度の罠を検出できる)
- **コード例**:
  ```php
  // Solitary: 依存先を全部 Test Double にして対象クラスだけを隔離
  public function testPlaceOrder_solitary(): void {
      $repo = $this->createMock(OrderRepository::class);
      $mailer = $this->createMock(Mailer::class);
      $repo->expects(self::once())->method('save');
      $mailer->expects(self::once())->method('send');

      $service = new OrderService($repo, $mailer);
      $service->place($order);
  }

  // Sociable: 依存先を実物のまま使う  結合度の罠を検出できる
  public function testPlaceOrder_sociable(): void {
      $repo = new InMemoryOrderRepository(); // 実装あり
      $mailer = new SpyMailer();              // 実装あり

      $service = new OrderService($repo, $mailer);
      $service->place($order);

      self::assertNotNull($repo->find($order->id));
      self::assertCount(1, $mailer->sent);
  }
  ```

### Characterization Test
- **読み**: キャラクタライゼーション テスト
- **定義**: レガシーコードの**現在の振る舞いを記録**するテスト  仕様が分からないコードに変更を加える前の安全網
- **使うとき**:
  - リファクタ前に **Characterization Test** で現状の振る舞いを固定すると、**変更時の安全性** が改善される。
- **コード例**:
  ```php
  // 仕様書のないレガシー計算ロジックに、現状の出力をそのまま固定する
  public function testLegacyShippingFee_currentBehavior(): void {
      $calculator = new LegacyShippingFeeCalculator();

      // 代表的な入力を片っ端から呼んで、今の結果をそのまま期待値にする
      self::assertSame(500,  $calculator->fee(weight: 0,  zone: 'A'));
      self::assertSame(800,  $calculator->fee(weight: 5,  zone: 'B'));
      self::assertSame(1500, $calculator->fee(weight: 20, zone: 'C'));
      // ※ 期待値が「正しい」かは不問  「現状こうなっている」をスナップショット化する
      //    リファクタで挙動が変わったら即検知できる安全網
  }
  ```

### 副作用境界
- **定義**: I/O や時刻取得や乱数等の副作用を、**コードの外周だけに集める** 設計  内側は Pure Function (別名: I/O 境界)
- **使うとき**:
  - **副作用境界** の観点から、{業務ロジック内} の I/O を外周に押し出すと、**業務ロジックのテスト容易性** が改善される。
- **コード例**:
  ```php
  // before: 業務ロジック内に I/O が散在  ロジック単体のテストが書けない
  class ReportService {
      public function generateMonthly(int $year, int $month): string {
          $orders = DB::query('SELECT ... WHERE year = ? AND month = ?', [$year, $month]);
          $rate = file_get_contents('https://exchange.example.com/rate');
          $report = '';
          foreach ($orders as $o) {
              $report .= ($o->total * (float) $rate) . "\n";
          }
          file_put_contents("/var/reports/{$year}-{$month}.txt", $report);
          return $report;
      }
  }

  // after: I/O を外周に押し出し  業務ロジックは Pure Function 化
  function buildReport(array $orders, float $rate): string {
      $report = '';
      foreach ($orders as $o) {
          $report .= ($o->total * $rate) . "\n";
      }
      return $report;
  }
  class ReportService {
      public function generateMonthly(int $year, int $month): string {
          $orders = $this->repository->findByMonth($year, $month);     // I/O (外周)
          $rate   = $this->exchangeApi->currentRate();                  // I/O (外周)
          $report = buildReport($orders, $rate);                        // Pure Function (内側)
          $this->writer->save("{$year}-{$month}.txt", $report);         // I/O (外周)
          return $report;
      }
  }
  ```
- **関連**: 副作用の局所化 / CQS / Functional Core Imperative Shell
