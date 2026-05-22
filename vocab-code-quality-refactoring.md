# コード品質 + リファクタリング

「ここちょっと汚い」を **「{臭い名} であるので {手法名} で {部分 X} を {修正後の姿 Y} にすると {改善される性質 Z} が改善される」** 表現に変換するための語彙


## TL;DR
- **臭い名 = 議論の出発点**  名前がないと「なんとなく」が消えない
- 手法名まで言えると「直す/直さない」の判断が分単位で進む
- **「適用すべきでないケース」を 1 個でも言えると指摘の説得力が増す**

## Contents
- コードの臭い (Code Smells)
	- Long Method
	- Long Parameter List
	- Feature Envy
	- Data Clump
	- Shotgun Surgery
	- Divergent Change
	- Primitive Obsession
	- Switch Statements
	- Speculative Generality
	- God Class
- リファクタリング手法 (Refactoring Catalog 抜粋)
	- Extract Method
	- Move Method (Move Field)
	- Replace Conditional with Polymorphism
	- Introduce Parameter Object
	- Replace Primitive with Object
	- Extract Class

---

## コードの臭い (Code Smells)

### Long Method
- **読み**: ロング メソッド
- **定義**: 長すぎるメソッド  メソッドが長すぎて目的が一目で読み取れない (20〜30 行が目安)
- **使うとき**:
  - **Long Method** である。**Extract Method** で {目的 A} と {目的 B} に分割すると、**メソッド名から意図が読めるようになり可読性とテスト容易性**が改善される。
- **適用すべきケース**:
  - 段落コメントで章立てされている
  - インデント 3 段以上
  - 1 メソッド内でスクロールが必要
- **適用すべきでないケース**:
  - **直列の SQL 構築 / DSL 構築** など分割すると逆に読みにくい箇所
  - パフォーマンスクリティカルな数値計算ループ (関数呼び出しオーバーヘッドが問題になる稀なケース)
- **トレードオフ**: 短さ ↔ 過剰分割で全体像が見えない  → 「**1 メソッド = 1 目的 + 1 抽象レベル**」を基準
- **コード例**:
  ```php
  // before: バリデーション・料金計算・永続化・通知が同居 (60行超)
  public function placeOrder(Order $order): void {
      if (empty($order->customerEmail)) {
          throw new InvalidArgumentException('email required');
      }
      if (count($order->items) === 0) {
          throw new InvalidArgumentException('cart is empty');
      }
      foreach ($order->items as $item) {
          if ($item->stock < $item->quantity) {
              throw new RuntimeException("out of stock: {$item->productId}");
          }
      }

      $subtotal = 0;
      foreach ($order->items as $item) {
          $subtotal += $item->price * $item->quantity;
      }
      $tax = (int) round($subtotal * 0.1);
      $shipping = $subtotal >= 5000 ? 0 : 500;
      $total = $subtotal + $tax + $shipping;

      $stmt = $this->db->prepare('INSERT INTO orders ...');
      $stmt->execute([$order->customerEmail, $total]);

      $this->mailer->send($order->customerEmail, 'Order Confirmed', '...');
  }

  // after: Extract Method で 1 メソッド = 1 目的に
  public function placeOrder(Order $order): void {
      $this->validate($order);
      $total = $this->calculateTotal($order);
      $this->persist($order, $total);
      $this->notify($order);
  }
  ```
- **関連**: SRP / Extract Method

### Long Parameter List
- **読み**: ロング パラメータ リスト
- **定義**: 引数 4 個以上 / boolean 引数で挙動分岐
- **使うとき**:
  - **Long Parameter List** である。**Introduce Parameter Object** で {x, y, z} をまとめると、**呼び出し側の可読性と引数順序間違いのリスク**が改善される。
- **適用すべきケース**:
  - 引数が 4 個以上
  - boolean フラグで挙動分岐
  - 同じ引数群がいつも一緒に渡される
- **適用すべきでないケース**:
  - **意味的に独立な 4 引数** を無理にまとめると凝集度が下がる
  - boolean 引数でも、enum 値 1 個程度の単純分岐なら関数分割の方が筋がいい
- **トレードオフ**: オブジェクト化 ↔ 呼び出し側のボイラープレート増  → 3 引数までは許容、4 個以上は要検討
- **コード例**:
  ```php
  // before: 引数 6 個 + 順序間違いリスク
  public function searchOrders(
      DateTime $startDate,
      DateTime $endDate,
      string $timezone,
      int $shopId,
      ?int $categoryId,
      ?int $customerId
  ): array { /* ... */ }

  // after: 期間と絞り込み条件を Value Object に
  public function searchOrders(
      DateRange $period,
      OrderFilter $filter
  ): array { /* ... */ }
  ```
- **関連**: Data Clump / Value Object / Introduce Parameter Object

### Feature Envy
- **読み**: フィーチャー エンビー
- **定義**: あるメソッドが自クラスより他クラスの getter を多用してる  振る舞いの置き場所が間違ってる
- **使うとき**:
  - **Feature Envy** である。**Move Method** で振る舞いを **データを多く持つ側のクラス** に移動すると、**そのクラスの凝集度と Tell Don't Ask 適合度**が改善される。
- **適用すべきケース**: `other.getA(); other.getB(); other.getC()` で計算を組み立ててる
- **適用すべきでないケース**:
  - **ViewModel / Presenter / Serializer** など、目的が他クラスの値を組み合わせて表示することそのもの
  - **Aggregate 跨ぎ**で本来 Domain Service に置くべき調整ロジック (Aggregate を肥大化させない)
- **トレードオフ**: 振る舞い集約 ↔ Aggregate 境界  → 「**Aggregate 内なら Move、跨ぐなら Domain Service**」
- **コード例**:
  ```php
  // before: Invoice が Customer の getter を多用して整形している
  class Invoice {
      public function mailingLabel(Customer $customer): string {
          $name = $customer->getFirstName() . ' ' . $customer->getLastName();
          $addr = $customer->getZipCode() . ' ' . $customer->getAddress();
          return "{$name}\n{$addr}";
      }
  }

  // after: 振る舞いを Customer 側に移動
  class Customer {
      public function mailingLabel(): string {
          return "{$this->firstName} {$this->lastName}\n{$this->zipCode} {$this->address}";
      }
  }
  class Invoice {
      public function mailingLabel(Customer $customer): string {
          return $customer->mailingLabel();
      }
  }
  ```
- **関連**: Tell Don't Ask / 凝集度 / Move Method

### Data Clump
- **読み**: データ クランプ
- **定義**: いつも一緒に登場する 3 つ以上の変数がメソッド引数・フィールドにバラバラに散らばる
- **使うとき**:
  - **Data Clump** である。**Extract Class** または **Introduce Parameter Object** で {x, y, z} を Value Object 化すると、**型による意味の明確化と引数順序ミスの予防**が改善される。
- **適用すべきケース**: 3 つの値が常にセットで現れる (例: `startDate, endDate, timezone`)
- **適用すべきでないケース**:
  - **偶然 3 つ揃っているだけ**で意味的に独立な変数 (DRY の罠と同様)
- **トレードオフ**: 集約 ↔ 単純な構造の解体コスト  → 「**3 箇所以上で同じ群れが出る**」を閾値に
- **コード例**:
  ```php
  // before: {start, end} が複数メソッドの引数にバラバラに登場
  function isOverlap(DateTime $startA, DateTime $endA, DateTime $startB, DateTime $endB): bool { /* ... */ }
  function durationMinutes(DateTime $start, DateTime $end): int { /* ... */ }
  function contains(DateTime $start, DateTime $end, DateTime $target): bool { /* ... */ }

  // after: DateRange Value Object に集約 ＆ 関連振る舞いも内側へ
  final class DateRange {
      public function __construct(
          public readonly DateTime $start,
          public readonly DateTime $end,
      ) {
          if ($start > $end) throw new InvalidArgumentException('start after end');
      }
      public function overlaps(DateRange $other): bool { /* ... */ }
      public function durationMinutes(): int { /* ... */ }
      public function contains(DateTime $target): bool { /* ... */ }
  }
  ```
- **関連**: Primitive Obsession / Value Object

### Shotgun Surgery
- **読み**: ショットガン サージェリー
- **定義**: 散弾銃手術  1 つの変更が複数クラスに飛び火する  「○○を変えるたびに 5 ファイル触る」
- **使うとき**:
  - **Shotgun Surgery** である。**Move Method / Move Field** で {関連処理} を **1 箇所に集約** すると、**変更時の修正漏れリスクと変更コスト**が改善される。
- **適用すべきケース**:
  - 同じ概念に対する処理が複数クラスにバラバラに散らばる
  - 仕様変更で 5 ファイル以上を触る
- **適用すべきでないケース**:
  - **Bounded Context 跨ぎ** (別 Context なのに無理に集約すると Context 境界が壊れる)
  - **クロスカット関心 (ロギング/権限/トランザクション)** は本来分散して当然
- **トレードオフ**: 集約 ↔ 関心分離  → 「**Bounded Context 内か外か**」で判定
- **関連**: 凝集度 / Divergent Change (対の臭い) / SRP

### Divergent Change
- **読み**: ダイバージェント チェンジ
- **定義**: 発散する変更  1 つのクラスが複数の理由で変更される (SRP 違反の現れ)
- **使うとき**:
  - **Divergent Change** である。**Extract Class** で {理由 A 担当} / {理由 B 担当} に分割すると、**変更時の影響範囲とレビュー範囲**が改善される。
- **適用すべきケース**: 同じクラスの修正履歴が、明らかに違う 2 つ以上の理由で交互に発生してる
- **適用すべきでないケース**:
  - **試作期/フェーズ 1** で要件が固まってない段階での先回り分割
  - 変更履歴が 1〜2 件しかなく統計的に判断できない
- **トレードオフ**: 分割の利益 ↔ 分割コスト  → 「**過去 3 回以上の異なる理由による変更**」を目安
- **関連**: SRP / Shotgun Surgery (対の臭い)

### Primitive Obsession
- **読み**: プリミティブ オブセッション
- **定義**: 業務上意味のある概念 (電話番号・金額・メールアドレス・郵便番号等) を、専用の型を作らず string / int といった汎用の基本型 (プリミティブ型) のまま扱い続ける状態  型で意味を表現できず、バリデーション漏れや単位混同 (例: JPY と USD を取り違える) を招く
- **使うとき**:
  - **Primitive Obsession** である。**Replace Primitive with Object** で {電話番号 / 金額 / 通貨} を Value Object 化すると、**型による不正値防止とバリデーション集約**が改善される。
- **適用すべきケース**: 電話番号・金額・通貨・郵便番号・メールアドレス等の業務概念が string/int で流通
- **適用すべきでないケース**:
  - **本当に汎用な数値/文字列** (汎用カウンタ、汎用ラベル) に Value Object 化を要求すると過剰
  - 既存コードベース全体に Primitive 前提が深く根付いてる場合、漸進的に導入しないと一斉変換は危険
- **トレードオフ**: 型安全 ↔ 変換コスト境界の増殖  → 業務概念 (金額・通貨・電話番号・メール等) が好相性
- **コード例**:
  ```php
  // before: メールアドレス・金額が string/int で流通、バリデーションが各所に散在
  function sendInvoice(string $email, int $amount, string $currency): void {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          throw new InvalidArgumentException('invalid email');
      }
      if ($amount < 0) throw new InvalidArgumentException('negative amount');
      if (!in_array($currency, ['JPY', 'USD'], true)) {
          throw new InvalidArgumentException('unsupported currency');
      }
      // ...
  }

  // after: Value Object 化 ＆ コンストラクタで不正値を弾く
  final class Email {
      public function __construct(public readonly string $value) {
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
              throw new InvalidArgumentException('invalid email');
          }
      }
  }
  final class Money {
      public function __construct(
          public readonly int $amount,
          public readonly Currency $currency,
      ) {
          if ($amount < 0) throw new InvalidArgumentException('negative amount');
      }
  }
  function sendInvoice(Email $to, Money $price): void {
      // 不正値は型レベルで弾かれているのでここは業務ロジックに集中できる
  }
  ```
- **関連**: Value Object / Data Clump / Replace Primitive with Object

### Switch Statements
- **読み**: スイッチ ステートメンツ
- **定義**: 種別を表す定数で switch/if 分岐が複数箇所に散らばる
- **使うとき**:
  - **Switch Statements** である。**Replace Conditional with Polymorphism** で種別ごとに振る舞いを分けると、**新種別追加時の OCP 適合**が改善される。
- **適用すべきケース**:
  - 同じ型コードに対する switch が 3 箇所以上にコピペ的に存在
  - 追加のたびに全箇所修正
- **適用すべきでないケース**:
  - **種別が 1〜2 個しかない** / 追加予定がない安定型
  - **データ取得の dispatch** (DB の type カラム読みだし直後の単純分岐) を無理にポリモーフィズム化
- **トレードオフ**: 拡張性 ↔ 抽象化コスト  → 「**追加実績 2 回以上**」が現実的閾値
- **コード例**:
  ```php
  // before: 種別ごとの switch が複数メソッドにコピペ
  class PaymentProcessor {
      public function fee(string $type, int $amount): int {
          switch ($type) {
              case 'card':   return (int) round($amount * 0.036);
              case 'bank':   return 200;
              case 'paypay': return (int) round($amount * 0.025);
          }
          throw new InvalidArgumentException("unknown: {$type}");
      }
      public function settlementDays(string $type): int {
          switch ($type) {
              case 'card':   return 3;
              case 'bank':   return 1;
              case 'paypay': return 7;
          }
          throw new InvalidArgumentException("unknown: {$type}");
      }
  }

  // after: PaymentMethod に振り分け  新種別追加は新クラス 1 個で完結
  interface PaymentMethod {
      public function fee(int $amount): int;
      public function settlementDays(): int;
  }
  final class CardPayment implements PaymentMethod {
      public function fee(int $amount): int { return (int) round($amount * 0.036); }
      public function settlementDays(): int { return 3; }
  }
  final class BankPayment implements PaymentMethod {
      public function fee(int $amount): int { return 200; }
      public function settlementDays(): int { return 1; }
  }
  // ... PayPayPayment 等
  ```
- **関連**: OCP / Strategy / State パターン

### Speculative Generality
- **読み**: スペキュレイティブ ジェネラリティ
- **定義**: 投機的一般化  「いつか使うかも」で作った抽象・拡張ポイントが使われてない
- **使うとき**:
  - **Speculative Generality** である。使われてない {抽象クラス / interface / オプション引数} を **削除** すると、**現スコープの認知負荷とメンテ対象範囲**が改善される。
- **適用すべきケース**:
  - 抽象クラスの実装が 1 つだけ
  - 使われてないオプション引数
  - 「将来用」コメントが残ってる
- **適用すべきでないケース**:
  - **公開ライブラリ / 公開 API** で後方互換のために残してる箇所
  - 監査/法令対応で意図的に拡張点を残してる箇所
- **トレードオフ**: 拡張性 ↔ 認知負荷  → YAGNI と表裏一体
- **関連**: YAGNI / KISS / Collapse Hierarchy

### God Class
- **読み**: ゴッド クラス
- **定義**: 複数の責務 (業務ロジック・永続化・通知・整形等) を 1 つのクラスに詰め込んだ結果、行数が大きく変更時の影響範囲が広くなったクラス  Manager / Helper / Util / Service という名前に多い
- **使うとき**:
  - **God Class** である。**Extract Class** を繰り返して {責務 A/B/C} に分割すると、**変更時のレビュー範囲とテスト粒度**が改善される。
- **適用すべきケース**:
  - 1 クラスが 500 行以上
  - 業務ロジック・永続化・通知・整形を全部抱えてる
- **適用すべきでないケース**:
  - **Facade パターン**として意図的に集約してる入口クラス (実装は内部委譲)
  - データ構造クラス (DTO/Config) で行数が多いだけのケース
- **トレードオフ**: 分割の利益 ↔ 分割コスト + 再構成リスク  → 「**段階的に Extract**」が安全
- **コード例**:
  ```php
  // before: OrderManager が業務ロジック・永続化・通知・整形を全部抱える (500 行超)
  class OrderManager {
      public function placeOrder(Order $order): void { /* 80 行 */ }
      public function calculateTotal(Order $order): int { /* 50 行 */ }
      public function saveToDatabase(Order $order): void { /* 30 行 */ }
      public function sendConfirmationEmail(Order $order): void { /* 40 行 */ }
      public function exportCsv(array $orders): string { /* 60 行 */ }
      public function generateInvoicePdf(Order $order): string { /* 80 行 */ }
      // ... 他 10 メソッド
  }

  // after: 責務ごとに Extract Class
  class OrderService { public function placeOrder(Order $order): void { /* ... */ } }
  class OrderPricing { public function calculateTotal(Order $order): int { /* ... */ } }
  class OrderRepository { public function save(Order $order): void { /* ... */ } }
  class OrderNotifier { public function sendConfirmation(Order $order): void { /* ... */ } }
  class OrderCsvExporter { public function export(array $orders): string { /* ... */ } }
  class InvoicePdfGenerator { public function generate(Order $order): string { /* ... */ } }
  ```
- **関連**: SRP / 凝集度 / Extract Class

---

## リファクタリング手法 (Refactoring Catalog 抜粋)

### Extract Method
- **読み**: エクストラクト メソッド
- **定義**: 1 つのメソッドの一部分を切り出して新しいメソッドにし、呼び出しに置き換えるリファクタリング手法  段落コメントの位置や繰り返し箇所が切り出しの目印
- **使うとき**:
  - **Long Method** であり段落コメントで章立てされている、または同じ処理が複数箇所で繰り返されている。**Extract Method** で目的ごとの単位に切り出すと、**本流メソッドの読み下しコストとメソッド名による意図の伝達**が改善される。
- **適用すべきケース**:
  - 段落コメントで章立てされた長関数
  - 同じ処理が 2 箇所以上に重複している
  - インデント 3 段以上で本流が見えない
- **適用すべきでないケース**:
  - 直列の SQL / DSL 構築のように分割すると逆に読みにくい箇所
  - 1〜2 行で完結する処理を無理に切り出すケース (ジャンプコストが上回る)
- **トレードオフ**: 切り出しによる意図の明示 ↔ 過剰分割で読み手のジャンプ回数が増える  → 「**1 メソッド = 1 目的 + 1 抽象レベル**」を基準
- **コード例**:
  ```php
  // before: 段落コメントで章立てされたメソッド
  public function generateReport(array $orders): string {
      // 月別集計
      $monthly = [];
      foreach ($orders as $o) {
          $key = $o->date->format('Y-m');
          $monthly[$key] = ($monthly[$key] ?? 0) + $o->total;
      }

      // HTML 整形
      $html = '<table>';
      foreach ($monthly as $month => $sum) {
          $html .= "<tr><td>{$month}</td><td>{$sum}</td></tr>";
      }
      $html .= '</table>';

      return $html;
  }

  // after: 段落コメントの位置で Extract Method  本流の意図が一目で読める
  public function generateReport(array $orders): string {
      $monthly = $this->aggregateByMonth($orders);
      return $this->renderHtmlTable($monthly);
  }
  ```
- **関連**: Long Method / SRP / 1 抽象レベル

### Move Method / Move Field
- **読み**: ムーブ メソッド / ムーブ フィールド
- **定義**: メソッドやフィールドを、より関連の強いクラスに移動するリファクタリング手法  振る舞いをデータを多く持つ側のクラスに寄せることで凝集度を上げる
- **使うとき**:
  - **Feature Envy** で自クラスより他クラスの getter を多用している、または **Shotgun Surgery** で 1 つの変更が複数ファイルに飛び火している。**Move Method / Move Field** でデータを多く持つ側のクラスに振る舞いを移動すると、**凝集度と変更時の修正漏れリスク**が改善される。
- **適用すべきケース**:
  - メソッドが自クラスのフィールドより他クラスの getter を多く参照している
  - 同じデータに対する操作が複数クラスに散らばっている
  - フィールドが本来所属すべきクラスと別のクラスに置かれている
- **適用すべきでないケース**:
  - Aggregate 境界を跨ぐ移動 (Domain Service の検討が先)
  - 移動先クラスが既に God Class 化しているケース (先に Extract Class)
- **トレードオフ**: 振る舞い集約 ↔ Aggregate 境界破壊  → 「**Aggregate 内なら Move、跨ぐなら Domain Service**」を基準
- **コード例**:
  ```php
  // before: Order が Customer の内部 (memberRank) で割引率を計算 (Feature Envy)
  class Order {
      public function discountAmount(Customer $customer, int $subtotal): int {
          $rate = 0;
          if ($customer->memberRank === 'gold')   $rate = 0.15;
          if ($customer->memberRank === 'silver') $rate = 0.05;
          return (int) round($subtotal * $rate);
      }
  }

  // after: 振る舞いを Customer 側に Move Method
  class Customer {
      public function discountRate(): float {
          return match ($this->memberRank) {
              'gold'   => 0.15,
              'silver' => 0.05,
              default  => 0.0,
          };
      }
  }
  class Order {
      public function discountAmount(Customer $customer, int $subtotal): int {
          return (int) round($subtotal * $customer->discountRate());
      }
  }
  ```
- **関連**: Feature Envy / Tell Don't Ask / 凝集度

### Replace Conditional with Polymorphism
- **読み**: リプレイス コンディショナル ウィズ ポリモーフィズム
- **定義**: 型コードや種別による条件分岐を、サブクラスへの委譲に置き換えるリファクタリング手法  種別ごとの振る舞いを別クラスに分割する
- **使うとき**:
  - **Switch Statements** で種別ごとの switch / if 連鎖が複数箇所にコピペされている、または型コードによる分岐が広がっている。**Replace Conditional with Polymorphism** でサブクラスに振り分けると、**種別追加時の改修箇所と分岐の見通し**が改善される。
- **適用すべきケース**:
  - 同じ種別 (channel / type / category) による分岐が複数のメソッドにコピペされている
  - 新しい種別を追加するたびに複数箇所を触る必要がある
  - 種別ごとに振る舞いが大きく異なる
- **適用すべきでないケース**:
  - 分岐が 1 箇所だけで他にコピペされていないケース (関数分割や enum で十分)
  - 種別ごとの振る舞いが 1 行程度の単純な差分
- **トレードオフ**: 拡張性 ↔ クラス数の増加  → 「**同じ分岐が 2 箇所以上で出現**」を閾値に
- **コード例**:
  ```php
  // before: 1 メソッド内に複数の channel 分岐 (新チャネル追加でここを毎回触る)
  class Notifier {
      public function notify(string $channel, string $message): void {
          if ($channel === 'email') {
              $this->mailer->send($message);
          } elseif ($channel === 'slack') {
              $this->slack->post($message);
          } elseif ($channel === 'sms') {
              $this->sms->send($message);
          }
      }
  }

  // after: NotificationChannel interface に振り分け  追加は新クラス 1 個で完結
  interface NotificationChannel {
      public function notify(string $message): void;
  }
  final class EmailChannel implements NotificationChannel { /* ... */ }
  final class SlackChannel implements NotificationChannel { /* ... */ }
  final class SmsChannel implements NotificationChannel { /* ... */ }

  class Notifier {
      public function notify(NotificationChannel $channel, string $message): void {
          $channel->notify($message);
      }
  }
  ```
- **関連**: Switch Statements / OCP / Strategy パターン

### Introduce Parameter Object
- **読み**: イントロデュース パラメータ オブジェクト
- **定義**: 常に一緒に渡される複数の引数を 1 つのオブジェクトにまとめるリファクタリング手法  Data Clump の解消に使う
- **使うとき**:
  - **Long Parameter List** で引数が 4 個以上ある、または **Data Clump** で常に一緒に登場する変数群が散らばっている。**Introduce Parameter Object** で関連引数をまとめると、**呼び出し側の可読性と引数順序間違いの防止**が改善される。
- **適用すべきケース**:
  - 引数が 4 個以上のメソッドが複数ある
  - 同じ引数群が複数メソッドの引数に繰り返し現れる
  - 引数の意味が単独では理解しにくい (start と end は対で意味を持つ等)
- **適用すべきでないケース**:
  - 意味的に独立な 4 引数を無理にまとめるケース (凝集度が下がる)
  - 1 メソッドでしか使わない引数群 (オブジェクト化のコストが上回る)
- **トレードオフ**: 集約による意図の明示 ↔ クラス追加のボイラープレート  → 「**3 箇所以上で同じ群れが出現**」を閾値に
- **コード例**:
  ```php
  // before: 同じ 4 引数を複数メソッドが受け取る
  function createReport(DateTime $from, DateTime $to, string $tz, int $shopId): Report { /* ... */ }
  function exportReport(DateTime $from, DateTime $to, string $tz, int $shopId): string { /* ... */ }

  // after: ReportPeriod にまとめる  順序ミスもなくなる
  final class ReportPeriod {
      public function __construct(
          public readonly DateTime $from,
          public readonly DateTime $to,
          public readonly string $timezone,
          public readonly int $shopId,
      ) {}
  }
  function createReport(ReportPeriod $period): Report { /* ... */ }
  function exportReport(ReportPeriod $period): string { /* ... */ }
  ```
- **関連**: Long Parameter List / Data Clump / Value Object

### Replace Primitive with Object
- **読み**: リプレイス プリミティブ ウィズ オブジェクト
- **定義**: 業務上意味のある値を string / int といったプリミティブ型のまま扱うのをやめ、専用の型 (Value Object) に置き換えるリファクタリング手法
- **使うとき**:
  - **Primitive Obsession** で業務概念 (電話番号 / 金額 / メール / 郵便番号等) を string / int のまま流通させている。**Replace Primitive with Object** で Value Object に置き換えると、**型レベルでの不正値の弾きと単位混同の防止**が改善される。
- **適用すべきケース**:
  - 同じ業務概念に対するバリデーションが複数箇所にコピペされている
  - 通貨や単位の異なる値を同じ型で扱っていて取り違えのリスクがある
  - メソッドシグネチャから引数の意味が読み取れない (`string $a, string $b` 等)
- **適用すべきでないケース**:
  - 一時的なローカル変数や DB テーブル ID 等、業務概念を持たない値
  - 既存コードへの影響範囲が大きく、段階的移行コストが利益を上回るケース
- **トレードオフ**: 型による安全性 ↔ 型追加と境界での詰め替えコスト  → 「**バリデーションが 2 箇所以上で重複**」を閾値に
- **コード例**:
  ```php
  // before: 電話番号が string で流通  バリデーションが呼び出し側に散らばる
  function sendSms(string $phone, string $message): void {
      if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
          throw new InvalidArgumentException('invalid phone number');
      }
      // ...
  }

  // after: PhoneNumber Value Object 化  コンストラクタで不正値を弾く
  final class PhoneNumber {
      public function __construct(public readonly string $value) {
          if (!preg_match('/^\+?[0-9]{10,15}$/', $value)) {
              throw new InvalidArgumentException('invalid phone number');
          }
      }
  }
  function sendSms(PhoneNumber $to, string $message): void {
      // 不正値は型レベルで弾かれている
  }
  ```
- **関連**: Primitive Obsession / Value Object / DDD

### Extract Class
- **読み**: エクストラクト クラス
- **定義**: 1 つのクラスが持つ複数の責務を別クラスに切り出して委譲するリファクタリング手法  God Class や Divergent Change の解消に使う
- **使うとき**:
  - **Divergent Change** で 1 つのクラスが複数の理由で変更される、または **God Class** で責務が肥大化している。**Extract Class** で責務ごとに別クラスへ切り出すと、**変更時のレビュー範囲と単一責任性**が改善される。
- **適用すべきケース**:
  - 同じクラスの修正履歴が、明らかに違う複数の理由で交互に発生している
  - クラスのメソッド数が 10 個を超え、メソッド群が責務別にグループ化できる
  - 一部のフィールドだけを参照するメソッド群が固まっている
- **適用すべきでないケース**:
  - クラスが小さく、責務が 1 つに収まっているケース
  - 分割すると Feature Envy が大量発生するケース (境界の引き直しが先)
- **トレードオフ**: 単一責任 ↔ クラス間の連携コスト  → 「**過去 3 回以上の異なる理由による変更**」または「**メソッド群が責務でグループ化できる**」を目安
- **コード例**:
  ```php
  // before: 1 クラスが業務ロジック + 永続化 + 通知 を全部担っている
  class OrderService {
      public function placeOrder(Order $order): void { /* 業務 */ }
      public function saveToDatabase(Order $order): void { /* 永続化 */ }
      public function sendConfirmationEmail(Order $order): void { /* 通知 */ }
  }

  // after: 責務ごとに Extract Class して委譲
  class OrderService {
      public function __construct(
          private OrderRepository $repository,
          private OrderNotifier $notifier,
      ) {}
      public function placeOrder(Order $order): void {
          $this->repository->save($order);
          $this->notifier->sendConfirmation($order);
      }
  }
  class OrderRepository {
      public function save(Order $order): void { /* ... */ }
  }
  class OrderNotifier {
      public function sendConfirmation(Order $order): void { /* ... */ }
  }
  ```
- **関連**: God Class / Divergent Change / SRP
