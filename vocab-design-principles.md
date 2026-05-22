# 設計原則

「なんとなく分割した方がいい」を **「{原則名} の観点から {現状 X} を {提案 Y} にすると {改善される性質 Z} が改善される」** の 4 点セット表現に変換するための語彙


## TL;DR
- 各原則の「**適用すべきでないケース**」を覚えるだけで「なんとなく」発言は半分以上消える  原則は「**逆方向にも切れる刃**」と理解する
- 4 点セット表現ができるようになったら、次は「**今この場面でこの原則を出すのは妥当か**」のメタ判断 ([vocab-judgment-tradeoffs](vocab-judgment-tradeoffs.md) に進む)

## Contents
- SOLID
	- SRP
	- OCP
	- LSP
	- ISP
	- DIP
- SOLID 以外の頻出原則
	- DRY
	- KISS
	- YAGNI
	- 凝集度・結合度
	- Tell Don't Ask
	- CQS
	- Law of Demeter

---

## SOLID

### SRP
- **読み**: エスアールピー (Single Responsibility Principle)
- **定義**: 単一責任の原則  1 クラスは 1 つのアクター (変更を要求してくる人/役割) にのみ責任を持つ
- **使うとき**:
  - **SRP** の観点から、{業務ロジック A} と {永続化 B} を抱える {クラス C} を分割すると、**変更時の影響範囲**と**テスト容易性**が改善される。
- **適用すべきケース**:
  - 1 クラスで 2 種類以上の理由で変更が走る (Divergent Change)
  - レビューで「ここは {役割 A 担当} に確認」「ここは {役割 B 担当}」と言いたくなる
- **適用すべきでないケース**
  - **過剰分割**: 1 メソッドしか持たないクラスが大量生成され、可読性が逆に落ちる
  - 業務的に「同じアクター」の処理を機械的に分けて Shotgun Surgery 化させる
  - フェーズ 1 の試作コード (要件が固まっていない時に細分化しても無駄)
- **トレードオフ**:
  - 分けすぎ ↔ 1 まとめすぎ  → 「**変更理由の数 = クラスの数**」が目安、見た目の行数で決めない
- **コード例**:
  ```php
  // before: 業務ロジック + 永続化 + 整形 (請求書 PDF) が同居  3 アクターの変更で全員ここを触る
  class Order {
      public function calculateTotal(): int { /* 業務ロジック */ }
      public function save(PDO $db): void { /* 永続化 */ }
      public function toInvoicePdf(): string { /* 整形 */ }
  }

  // after: 1 クラス = 1 アクター
  class Order {
      public function calculateTotal(): int { /* ... */ }
  }
  class OrderRepository {
      public function save(Order $order): void { /* ... */ }
  }
  class InvoicePdfRenderer {
      public function render(Order $order): string { /* ... */ }
  }
  ```
- **関連**: 凝集度 / OCP / Aggregate / Divergent Change

### OCP
- **読み**: オーシーピー (Open-Closed Principle)
- **定義**: 開放閉鎖の原則  拡張に開いて、修正に閉じている構造を目指す
- **使うとき**:
  - **OCP** の観点から、**種別追加のたびに switch を書き換える現状** を **Strategy/Polymorphism** に変えると、**新種別追加時の既存コード非侵襲性**が改善される。
- **適用すべきケース**:
  - 種別/プラン/モードの追加が継続的に発生する箇所
  - 追加のたびに既存テストが軒並み壊れている
- **適用すべきでないケース**
  - **種別が 1〜2 個しかない**: 過剰抽象化、可読性低下
  - 種別追加がほぼない安定ドメイン (将来要件への投機的一般化)
- **トレードオフ**:
  - 拡張ポイントを増やす ↔ KISS / YAGNI  → 「**過去 2 回以上同種の追加が起きたら抽象化検討**」が現実的閾値
- **コード例**:
  ```php
  // before: 種別追加のたびに既存メソッドの switch を書き換える (修正に開いてる)
  class ShippingCalculator {
      public function fee(string $method, int $weight): int {
          switch ($method) {
              case 'standard': return $weight * 10;
              case 'express':  return $weight * 30 + 500;
          }
          throw new InvalidArgumentException("unknown: {$method}");
      }
  }
  // 新種別「cool便」追加 → ShippingCalculator::fee に case を追加する必要あり

  // after: ShippingMethod interface  追加は新クラス 1 個 既存コード非侵襲
  interface ShippingMethod {
      public function fee(int $weight): int;
  }
  final class StandardShipping implements ShippingMethod {
      public function fee(int $weight): int { return $weight * 10; }
  }
  final class ExpressShipping implements ShippingMethod {
      public function fee(int $weight): int { return $weight * 30 + 500; }
  }
  // 新種別「CoolShipping」は class 1 個追加するだけ  fee() の中は触らない
  ```
- **関連**: Replace Conditional with Polymorphism / Strategy / YAGNI / LSP

### LSP
- **読み**: エルエスピー (Liskov Substitution Principle)
- **定義**: リスコフの置換原則  親クラスの場所に子クラスを置いても呼び出し側が壊れない
- **使うとき**:
  - **LSP** の観点から、{子クラス D} が **NotImplementedException を投げる現状** は **継承を捨てて委譲** にすると、**契約の一貫性**が改善される。
- **適用すべきケース**:
  - 継承ツリーが既に存在し、子クラスの一部が親契約を満たせていない
  - 親型で受け取った変数に対し instanceof で分岐し始めた箇所
- **適用すべきでないケース**
  - 既存の親子の契約が成立している継承を「LSP違反かも」と疑い始めて議論を肥大化させる (要 evidence)
  - 言語機能上 interface で十分なところで継承を強行する議論
- **トレードオフ**:
  - 継承 ↔ 委譲  → **is-a が業務的に成立してなければ委譲**
- **コード例**:
  ```php
  // before: Penguin が Bird を継承するが fly() で例外を投げる (契約違反)
  class Bird {
      public function fly(): void { /* ... */ }
  }
  class Penguin extends Bird {
      public function fly(): void {
          throw new LogicException('penguins cannot fly');
      }
  }
  function migrate(Bird $bird): void {
      $bird->fly(); // Penguin を渡すと例外  呼び出し側で instanceof チェックが必要
  }

  // after: 能力で interface を分離  「飛べる鳥」だけを受け取る
  interface Flyable {
      public function fly(): void;
  }
  class Sparrow implements Flyable {
      public function fly(): void { /* ... */ }
  }
  class Penguin { /* fly を持たない */ }
  function migrate(Flyable $bird): void {
      $bird->fly(); // 契約が型レベルで保証される
  }
  ```
- **関連**: 継承より委譲 / Tell Don't Ask

### ISP
- **読み**: アイエスピー (Interface Segregation Principle)
- **定義**: インターフェース分離の原則  クライアントは使わないメソッドへの依存を強制されない
- **使うとき**:
  - **ISP** の観点から、{Read 専用利用者} が **CRUD 全部入り Repository に依存している現状** を **ReadOnly / WriteOnly interface に分離** すると、**テスト時のモック対象とビルド時の依存範囲**が改善される。
- **適用すべきケース**:
  - 1 interface に 5 メソッド以上、利用者ごとに使うメソッドが偏ってる
  - テストでモックする際に大量のスタブ実装が必要
- **適用すべきでないケース**
  - メソッド 2〜3 個の interface を細分化して **断片化** させる
  - 利用者が 1 つしかない interface を分けても意味がない
- **トレードオフ**:
  - 細かく分ける ↔ 統合する  → 「**実利用パターンが 2 種類以上に偏ってる**」が分割の閾値
- **コード例**:
  ```php
  // before: CRUD 全部入り Repository  Read 専用利用者もテストで全メソッドをモック必要
  interface OrderRepository {
      public function find(int $id): Order;
      public function findAll(): array;
      public function save(Order $order): void;
      public function delete(int $id): void;
  }
  class OrderListPresenter {
      public function __construct(private OrderRepository $repo) {}
      // find / findAll しか使わないのに、テストで save / delete もモックする羽目に
  }

  // after: 利用パターンで分離
  interface OrderReader {
      public function find(int $id): Order;
      public function findAll(): array;
  }
  interface OrderWriter {
      public function save(Order $order): void;
      public function delete(int $id): void;
  }
  class OrderListPresenter {
      public function __construct(private OrderReader $reader) {}
      // OrderReader だけモックすれば足りる
  }
  ```
- **関連**: SRP / DIP

### DIP
- **読み**: ディーアイピー (Dependency Inversion Principle)
- **定義**: 依存性逆転の原則  上位も下位も抽象 (interface) に依存する  詳細が抽象に依存する
- **使うとき**:
  - **DIP** の観点から、{UseCase} が **具象 Mailer に直接依存** している現状を **interface を Domain 側に切って DI 注入** すると、**テスト容易性とアダプタ差し替え可能性**が改善される。
- **適用すべきケース**:
  - Domain/UseCase が外部 API / DB / フレームワークを直接 new / static call している
  - テストでスタブが差し込めずに統合テストに頼っている
- **適用すべきでないケース**
  - **interface を 1 実装しかないのに切る**: ただの間接化、可読性低下
  - 設定ファイル/定数程度の依存にまで DI コンテナを挟む
- **トレードオフ**:
  - 抽象化のコスト ↔ テスト容易性  → 「**変更頻度 × テスト必要性**」で判定
- **コード例**:
  ```php
  // before: UseCase が具象 Mailer に直接依存  テストで本物の SMTP を叩く羽目に
  class PlaceOrderUseCase {
      public function execute(Order $order): void {
          $mailer = new SmtpMailer('smtp.example.com');
          $mailer->send($order->customerEmail, 'Confirmed');
      }
  }

  // after: Domain 側に Mailer interface を切って DI 注入  詳細 → 抽象 へ依存
  interface Mailer {
      public function send(string $to, string $subject): void;
  }
  class SmtpMailer implements Mailer { /* インフラ層の実装 */ }
  class FakeMailer implements Mailer { /* テスト用 */ }

  class PlaceOrderUseCase {
      public function __construct(private Mailer $mailer) {}
      public function execute(Order $order): void {
          $this->mailer->send($order->customerEmail, 'Confirmed');
      }
  }
  ```
- **関連**: Clean Architecture / Hexagonal / ISP / ACL

---

## SOLID 以外の頻出原則

### DRY
- **読み**: ドライ (Don't Repeat Yourself)
- **定義**: 同じ知識 (= 同じ意味を持つルール/事実) は 1 箇所だけに置く
- **使うとき**:
  - **DRY** の観点から、{バリデーションルール} が {Controller} と {Model} に二重定義されている現状を **1 箇所に集約** すると、**仕様変更時の整合性リスク**が改善される。
- **適用すべきケース**:
  - **意味的に同じ**ロジックが複数箇所に重複し、仕様変更時に同期取り漏れリスクがある
- **適用すべきでないケース**  (最重要)
  - **見た目が同じだが意味が違う**コードを共通化 → 後の仕様分岐で「if 分岐だらけの共通関数」に腐る
  - 偶然の一致 (Coincidental Duplication) を DRY 化すると、本来別の進化軌道を持つはずの 2 つを縛る
- **トレードオフ**:
  - DRY ↔ 凝集度  → 「**同じ知識かどうか**」を聞く  「同じ見た目だが違う理由で同じ」は別物として扱う (Rule of Three: 3 回目で抽象化)
- **コード例**:
  ```php
  // before: 「税率 10%」が Controller と Service に二重定義  仕様変更で同期漏れ事故
  class OrderController {
      public function preview(Request $req): Response {
          $subtotal = $req->input('subtotal');
          $tax = (int) round($subtotal * 0.10); // ← ここに 0.10
          return new Response(['tax' => $tax]);
      }
  }
  class OrderService {
      public function place(Order $order): void {
          $order->tax = (int) round($order->subtotal * 0.10); // ← ここにも 0.10
      }
  }

  // after: 「税率」という知識を 1 箇所に集約
  final class TaxPolicy {
      public const RATE = 0.10;
      public static function calculate(int $subtotal): int {
          return (int) round($subtotal * self::RATE);
      }
  }
  class OrderController {
      public function preview(Request $req): Response {
          return new Response(['tax' => TaxPolicy::calculate($req->input('subtotal'))]);
      }
  }
  class OrderService {
      public function place(Order $order): void {
          $order->tax = TaxPolicy::calculate($order->subtotal);
      }
  }
  ```
- **関連**: 凝集度 / OAOO / Speculative Generality

### KISS
- **読み**: キス (Keep It Simple, Stupid)
- **定義**: 単純に保て  シンプルでないものは正当化されなければならない
- **使うとき**:
  - **KISS** の観点から、{現状の汎用化} は要件 (Y) に対して過剰なので、**直球の素朴な実装** に戻すと、**読み手の認知負荷と保守コスト**が改善される。
- **適用すべきケース**:
  - 仕様にない汎用化、設定可能化、抽象クラスが入っている
  - 新人がコードを追えない (= 抽象が要件に勝ってる)
- **適用すべきでないケース**
  - 「シンプル」を理由に**正当な抽象化を拒否**する (例: 全部 if 分岐で書け論)
  - パフォーマンス要件があるのに「素朴に書け」と一蹴
- **トレードオフ**:
  - シンプル ↔ 拡張性  → 「**今の要件 + 確定済みの次要件**」までで設計、それ以上は YAGNI
- **関連**: YAGNI / 過剰設計 / Speculative Generality

### YAGNI
- **読み**: ヤグニ (You Aren't Gonna Need It)
- **定義**: 今いらない機能・抽象は作らない  実際に必要になったときに作る
- **使うとき**:
  - **YAGNI** の観点から、{将来要件 Z} のための **拡張ポイントを今入れる** のは保留にして、**実要件発生時に追加** すると、**現スコープの実装/レビュー/テスト総コスト**が改善される。
- **適用すべきケース**:
  - 「いつか使うかも」「将来◯◯になるかも」を理由とした抽象/設定/引数
  - 使われていない引数・拡張メソッド
- **適用すべきでないケース**
  - **後付けが極端に高コストな箇所** (DB スキーマ、公開 API 契約、データ移行が伴う構造) で YAGNI を振りかざす → 後で大事故
  - セキュリティ・監査・法令対応で「いつか必要」が高確率で確定してる箇所
- **トレードオフ**:
  - YAGNI ↔ 拡張性  → 「**変更コストの非対称性**」を見る  「後で足すのが安いなら今入れない」「後で足すのが高いなら今入れる」
- **関連**: KISS / Speculative Generality / Last Responsible Moment

### 凝集度・結合度
- **読み**: コヒージョン (Cohesion) / カップリング (Coupling)
- **定義**: 凝集度 = モジュール内の要素が同じ目的にどれだけ集まってるか / 結合度 = モジュール間の依存の強さ  ベストは「**高凝集・低結合**」
- **使うとき**:
  - **凝集度** の観点から、{目的 A} と {目的 B} が同じクラスにある現状を **機能単位で集約** すると、**変更時の局所性**が改善される。
  - **結合度** の観点から、{他クラスの内部実装} に触っている現状を **interface 経由** にすると、**実装変更時の波及範囲**が改善される。
- **適用すべきケース**:
  - 1 ファイル内で目的の違う処理がブロックごとに同居
  - 他クラスの protected/internal にアクセスして実装変更で連鎖的に壊れる
- **適用すべきでないケース**
  - **高凝集を求めるあまり過剰分割**して結合度が逆に上がる (= モジュール間呼び出しが激増)
  - 「低結合」を理由に**意味のないラップ層**を挟む
- **トレードオフ**:
  - 凝集度 ↔ 結合度  → 一方を最大化すると他方が悪化する局面がある  「**全体として最適**」を意識
- **関連**: SRP / DIP / Law of Demeter

### Tell Don't Ask
- **読み**: テル ドント アスク
- **定義**: オブジェクトに状態を聞いて判断するな  オブジェクトに「やれ」と指示しろ
- **使うとき**:
  - **Tell, Don't Ask** の観点から、{呼び出し側} で {対象オブジェクト} の状態を取り出して if 判定している現状を **対象オブジェクトに振る舞いを移譲** すると、**カプセル化と業務ロジックの集約度**が改善される。
- **適用すべきケース**:
  - getter チェーン + 呼び出し側 if が複数箇所に散らばってる
  - 内部状態の変更を呼び出し側が組み立ててる
- **適用すべきでないケース**
  - **読み取り専用の表示用データ取り出し** (ViewModel への詰め替え等) に Tell Don't Ask を要求して逆に冗長化
  - DTO / Value Object の値取得 (これらはそもそも振る舞いを持たない設計意図)
- **トレードオフ**:
  - 振る舞い集約 ↔ 貧血モデル回避 ↔ 表示用詰め替えコード  → **書き込み/状態変更を伴う処理** で特に効く
- **コード例**:
  ```php
  // before: 呼び出し側が Account の状態を聞いて判断  業務ルールが Account 外に漏れる
  class Account {
      public int $balance;
      public bool $frozen;
  }
  function withdraw(Account $account, int $amount): void {
      if ($account->frozen) {
          throw new RuntimeException('account is frozen');
      }
      if ($account->balance < $amount) {
          throw new RuntimeException('insufficient funds');
      }
      $account->balance -= $amount;
  }

  // after: Account に振る舞いを移譲  業務ルールが Account 内にカプセル化される
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
  }
  $account->withdraw($amount);
  ```
- **関連**: Feature Envy / カプセル化 / Aggregate / Anemic Domain Model

### CQS
- **読み**: シーキューエス (Command-Query Separation)
- **定義**: 状態を変える命令 (Command) と値を返す問い合わせ (Query) を 1 メソッドに同居させない
- **使うとき**:
  - **CQS** の観点から、{getXxx()} が **副作用 (キャッシュ書換 / ログ出力) を持つ** 現状を **Command と Query に分離** すると、**呼び出し側の予測可能性とテスト容易性**が改善される。
- **適用すべきケース**:
  - getter / find 系メソッドが裏で書き込みを行っている
  - 「副作用があると思ってなかった」系のバグが頻発
- **適用すべきでないケース**
  - **冪等な lazy initialization** (キャッシュ初期化等) まで分離を要求すると過剰
  - パフォーマンス上 1 アクセスで取得+更新が必要な箇所 (DB の UPDATE...RETURNING 等)
- **トレードオフ**:
  - 純粋さ ↔ 性能  → 「**呼び出し側が副作用を予測できるか**」を基準にする
- **コード例**:
  ```php
  // before: getNextId() が裏で DB の連番を進めている (取得のはずが副作用持ち)
  class IdGenerator {
      public function getNextId(): int {
          $this->db->exec('UPDATE seq SET val = val + 1');
          return (int) $this->db->query('SELECT val FROM seq')->fetchColumn();
      }
  }
  $id = $generator->getNextId(); // 「取得」のつもりが状態が進む  2 度呼ぶと値がズレる

  // after: Command と Query を分離  名前で副作用の有無が予測できる
  class IdGenerator {
      public function issue(): int { // Command: 状態を進めて発番
          $this->db->exec('UPDATE seq SET val = val + 1');
          return (int) $this->db->query('SELECT val FROM seq')->fetchColumn();
      }
      public function current(): int { // Query: 純粋に取得 (副作用なし)
          return (int) $this->db->query('SELECT val FROM seq')->fetchColumn();
      }
  }
  ```
- **関連**: 副作用の局所化 / CQRS

### Law of Demeter
- **読み**: ロー オブ デメテル
- **定義**: デメテルの法則 / 最小知識の原則  自分の直接の友達だけと話す  友達の友達 (孫オブジェクト) には触らない
- **使うとき**:
  - **デメテルの法則** の観点から、{a.getB().getC().doSomething()} のチェーンを **B に中継メソッド** を生やすと、**B の内部構造変更時の波及範囲**が改善される。
- **適用すべきケース**:
  - 長いメソッドチェーンが複数箇所にコピペされてる
  - 中間オブジェクトの構造変更で離れたクラスが壊れる
- **適用すべきでないケース**
  - **Fluent Interface / Builder** (意図的なメソッドチェーン) に対して機械的に適用
  - 単純な DTO/データ構造へのアクセス (`config.db.host` 等) に過剰反応
- **トレードオフ**:
  - カプセル化 ↔ 中継メソッドの増殖  → 「**チェーンの先が振る舞いか、ただのデータか**」で判定
- **コード例**:
  ```php
  // before: 孫オブジェクトの内部までチェーン  Customer の構造変更で全箇所が壊れる
  function applyCouponLimit(Order $order, Coupon $coupon): void {
      $rank = $order->getCustomer()->getProfile()->getMemberRank();
      if ($rank === 'gold') {
          // ...
      }
  }

  // after: Customer に中継メソッドを生やす  チェーンが切れて結合度が下がる
  class Customer {
      public function memberRank(): string {
          return $this->profile->getMemberRank();
      }
  }
  function applyCouponLimit(Order $order, Coupon $coupon): void {
      $rank = $order->getCustomer()->memberRank();
      if ($rank === 'gold') {
          // ...
      }
  }
  ```
- **関連**: 結合度 / Tell Don't Ask / カプセル化
