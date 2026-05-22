# 認知負荷

「これ読みづらい」「ややこしい」を **「{認知負荷の原則} の観点から {現状 X} を {提案 Y} にすると {読み手の負荷 Z} が下がる」** 表現に変換するための語彙
ワーキングメモリの限界 (4±1 チャンク) を前提に、**コード領域** で「読み手の頭に乗る量」を下げる共通言語

UI / 文書 / 会話の認知負荷は別の語彙集で扱う (第二フェーズで取り込む予定)

## TL;DR
- 認知負荷を語る前に **「自分の知識不足を負荷問題と取り違えてないか」** を確認する  これが最大のノイズ源
- 同じ原則 (チャンク数を減らす / Schema を活用) はコード以外の領域 (UI / 文書 / 会話) でも効く
- 「ワーキングメモリが小さい人」を前提にすると、**全員にとって楽** になる  上振れ最適化より下限保証
- 認知負荷削減と「情報削減」は別物  Intrinsic は保ち Extraneous を削るのが本質

## Contents
- 人間の認知特性 (前提)
	- Cognitive Load Theory
	- Intrinsic / Extraneous / Germane Load
	- Working Memory
	- Chunking
	- Schema
- コード表現
	- 命名の予測可能性
	- Levels of Indirection
	- ネストの深さ
	- 状態の散在 vs 局在
	- Context Switch コスト
	- Consistency
- トレードオフ・判断軸
	- 認知負荷 vs 表現の正確性
	- 抽象化 vs 学習コスト
	- DRY vs 局所性
	- Expert vs Novice Audience

---

## 人間の認知特性 (前提)

### Cognitive Load Theory
- **読み**: コグニティブ ロード セオリー
- **定義**: 認知負荷理論  学習者/読み手のワーキングメモリには有限の容量があり、容量を超えた情報は処理できない (John Sweller)
- **使うとき**: **Cognitive Load Theory** の観点から、{1 画面に 12 個の操作} を **主操作 3 + サブメニュー** に分けると **同時保持チャンク数** が下がる。
- **適用すべきケース**:
  - 学習/オンボーディング設計
  - 複雑な業務画面
  - 教材構造
- **適用すべきでないケース**: 専門家向けの密度高い ダッシュボード (チャンク数の上限が専門家では拡張済み)
- **トレードオフ**: 認知負荷を下げる ↔ 一度に見せる情報量
- **関連**: Working Memory / Chunking / Intrinsic Load

### Intrinsic / Extraneous / Germane Load
- **読み**: イントリンシック / エクストラニアス / ジャーメイン ロード
- **定義**: 種の認知負荷  Intrinsic = 題材本来の難しさ / Extraneous = 表現の悪さで生じる余計な負荷 / Germane = 学習のための有益な負荷  下げるのは Extraneous、保つのは Intrinsic + Germane
- **使うとき**: これは **Extraneous Load** が高い。{見出し階層 / 用語不統一} を直すと **題材の難しさは変えずに** 読みやすくなる。
- **適用すべきケース**: 「複雑」と言われた時の切り分け  どの種類の負荷が問題かを特定する
- **適用すべきでないケース**: Intrinsic を「Extraneous だ」と誤認して情報を削る (= 内容が薄くなる)
- **トレードオフ**: 表現を整える工数 ↔ 削減できる Extraneous Load
- **関連**: Cognitive Load Theory

### Working Memory
- **読み**: ワーキング メモリ
- **定義**: 作業記憶  短時間 (秒〜数十秒) 情報を保持・操作する記憶領域  Miller の 7±2、Cowan の修正版で 4±1 チャンクが現実的上限
- **使うとき**: **Working Memory** の観点から、{1 ステップで 7 項目} を入力させるのを **3 項目 × 段階入力** に分けると **同時保持コスト** が下がる。
- **適用すべきケース**:
  - フォーム設計
  - API パラメータ数
  - 関数引数
  - 設計議論の論点数
- **適用すべきでないケース**: バッチ処理パラメータ等、人間が頭に乗せる必要がない箇所
- **トレードオフ**: 段階化 ↔ 1 画面/1 関数で完結する利便性
- **関連**: Chunking / Long Parameter List / Miller's Law

### Chunking
- **読み**: チャンキング
- **定義**: チャンク化  関連する情報を 1 つのまとまり (チャンク) として扱うことで保持できる情報量を増やす技法
- **使うとき**: **Chunking** で、{バラバラの引数 6 個} を **意味のあるオブジェクト 2 個** にまとめると **ワーキングメモリの占有量** が下がる。
- **適用すべきケース**:
  - 引数群
  - フォーム項目
  - メニュー
  - リスト表示
- **適用すべきでないケース**: チャンクの**名前**が曖昧で「何のまとまりか」が伝わらない場合 (むしろ Extraneous Load 増)
- **トレードオフ**: まとめる ↔ 個別に名前を付けないと中身が見えない
- **関連**: Introduce Parameter Object / Working Memory / Schema

### Schema
- **読み**: スキーマ
- **定義**: スキーマ  長期記憶に格納された知識構造  既存スキーマがあれば新情報は「型に当てる」だけで負荷が下がる
- **使うとき**: 読み手の既存 Schema (例: REST 規約 / MVC 構造) に沿うと **新規学習負荷** が下がる。
- **適用すべきケース**:
  - 慣習に沿う命名・配置
  - 既存パターンの採用
  - ドキュメント構造の定型化
- **適用すべきでないケース**: スキーマが間違ってる箇所で「慣習」を盾に悪い設計を維持
- **トレードオフ**: 慣習踏襲 ↔ より良い設計への移行コスト
- **関連**: Consistency / Ubiquitous Language

---

## コード表現

### 命名の予測可能性
- **定義**: 名前から振る舞い・型・スコープが予測できる度合い  予測が当たればチャンク 1 つで済む、外れると関数本体を読む必要が発生
- **使うとき**: {getUserList} が active のみ返すのは予測外。**getActiveUsers** に改名すると **本体確認コスト** が下がる。
- **適用すべきケース**:
  - 公開 API
  - よく呼ばれる関数
  - クラス名
- **適用すべきでないケース**: ローカル変数の細部 (i, x 等) を過度に名付ける (むしろ可読性低下)
- **トレードオフ**: 名前の長さ ↔ 予測可能性
- **コード例**:
  ```php
  // before: 名前から振る舞いが予測できない  本体を読まないと挙動が分からない
  function getUserList(): array {
      // 実は active のみ返す (本体を読まないと分からない)
      return User::where('status', 'active')->get();
  }
  $users = getUserList(); // 「全ユーザー？ active のみ？」呼び出し側で迷う

  // after: 名前から振る舞いが予測できる  本体確認不要
  function getActiveUsers(): array {
      return User::where('status', 'active')->get();
  }
  function getAllUsers(): array {
      return User::all();
  }
  $users = getActiveUsers(); // 名前で意図が伝わる
  ```
- **関連**: Schema / Consistency

### Levels of Indirection
- **読み**: レベルス オブ インダイレクション
- **定義**: 間接化の階層  「呼び出し→ラップ→ラップ→実装」のように間接化が重なると追跡に必要なチャンクが指数的に増える  "Any problem can be solved by adding another layer of indirection — except the problem of too many layers of indirection" (David Wheeler)
- **使うとき**: **Levels of Indirection** が深い。{3 段の wrapper} を **2 段に圧縮** すると **追跡コスト** が下がる。
- **適用すべきケース**:
  - ラッパー連鎖が長いコード
  - Facade の Facade 化
- **適用すべきでないケース**: 境界のための必要な間接化 (DIP / ACL) を「層が多い」と削る
- **トレードオフ**: 抽象化の利益 ↔ 追跡コスト
- **コード例**:
  ```php
  // before: 3 段の wrapper を辿らないと実装に到達できない  ファイルを 3 つ開く必要
  class OrderFacade {
      public function place(array $items): void { $this->delegate->place($items); }
  }
  class OrderDelegate {
      public function place(array $items): void { $this->handler->execute($items); }
  }
  class OrderHandler {
      public function execute(array $items): void {
          // 本当の処理はここに 50 行
      }
  }

  // after: 不要な間接化を圧縮  入口 (Facade) と実装 (Handler) の 2 段に
  class OrderFacade {
      public function place(array $items): void {
          $this->handler->execute($items);
      }
  }
  class OrderHandler {
      public function execute(array $items): void {
          // 本当の処理はここに 50 行
      }
  }
  ```
- **関連**: KISS / YAGNI / Inline Method

### ネストの深さ
- **定義**: if/for/try のネストレベル  3 段超えると分岐の組み合わせがワーキングメモリを超えやすい
- **使うとき**: **ネスト 4 段** が出てる。**ガード節 + Extract Method** で **2 段に下げる** と分岐の同時保持コストが下がる。
- **適用すべきケース**:
  - コードレビューで深いネストを見つけた時
  - 長い関数
- **適用すべきでないケース**: フラット化のために多数の小関数を作って **追跡コスト** に転嫁
- **トレードオフ**: ネストの深さ ↔ メソッド数
- **コード例**:
  ```php
  // before: if ネスト 4 段  分岐の組み合わせを頭に乗せ続けないと読めない
  public function chargeDiscountedFee(?Customer $customer, int $amount): int {
      if ($customer !== null) {
          if (!$customer->frozen) {
              if ($customer->memberRank === 'gold') {
                  if ($amount > 0) {
                      return (int) round($amount * 0.9);
                  }
              }
          }
      }
      return $amount;
  }

  // after: ガード節で前提条件を早期 return  本流が 1 段に
  public function chargeDiscountedFee(?Customer $customer, int $amount): int {
      if ($customer === null)                  return $amount;
      if ($customer->frozen)                   return $amount;
      if ($customer->memberRank !== 'gold')    return $amount;
      if ($amount <= 0)                        return $amount;

      return (int) round($amount * 0.9);
  }
  ```
- **関連**: 早期 return / Extract Method / Long Method

### 状態の散在 vs 局在
- **定義**: 状態 (変更されるデータ) が複数モジュールに散らばると、ある瞬間の全体状態を頭に再構築する必要が発生する
- **使うとき**: 状態が散在。{注文状態が Controller / Service / View に分散} を **1 つの状態オブジェクトに集約** すると **状態追跡のワーキングメモリのコスト** が下がる。
- **適用すべきケース**:
  - 大規模 UI
  - 同期処理が複雑
  - バグの再現が難しい箇所
- **適用すべきでないケース**: 性能要件で意図的に状態分散している箇所 (Cache 等)
- **トレードオフ**: 局在化 ↔ アクセスの利便性
- **コード例**:
  ```php
  // before: 1 つの注文に関する表示判定状態が Controller / Service / View に散らばる
  class OrderController {
      public function show(Request $req): View {
          $order = OrderModel::find($req->id);
          $isEditable  = !$order->isClosed && $req->user()->canEdit;  // Controller でフラグ計算
          $statusLabel = $this->service->statusLabel($order);          // Service でラベル変換
          $total       = '¥' . number_format($order->total);           // ここでも整形
          return view('order.show', compact('order', 'isEditable', 'statusLabel', 'total'));
      }
  }
  // View 側でもさらに表示判定があり、状態追跡で 3 〜 4 箇所を読む必要

  // after: 表示状態を 1 つの ViewModel に集約  全体像が 1 箇所で見える
  final class OrderViewModel {
      public function __construct(
          public readonly OrderId $id,
          public readonly string $statusLabel,
          public readonly bool $isEditable,
          public readonly string $totalText,
      ) {}
      public static function build(Order $order, User $viewer): self {
          return new self(
              id: $order->id,
              statusLabel: self::label($order->status),
              isEditable: !$order->isClosed() && $viewer->canEdit($order),
              totalText: '¥' . number_format($order->total()),
          );
      }
      private static function label(OrderStatus $s): string { /* ... */ }
  }
  class OrderController {
      public function show(Request $req): View {
          $vm = OrderViewModel::build($this->orders->find($req->id), $req->user());
          return view('order.show', ['vm' => $vm]);
      }
  }
  ```
- **関連**: 副作用の局所化 / Aggregate / Tell Don't Ask

### Context Switch
- **読み**: コンテキスト スイッチ
- **定義**: コスト  別のファイル/別のレイヤ/別の言語に視点を移すたびにワーキングメモリが再ロードされる  ジャンプが多いコードは負荷が高い
- **使うとき**: **Context Switch** が頻発。{1 機能で 5 ファイル開く} を **関連処理を 1 箇所にまとめる** と **追跡 ワーキングメモリのロード回数** が下がる。
- **適用すべきケース**: 修正のために多数のファイルを開く必要がある時
- **適用すべきでないケース**: SRP に従った正当な分離まで「跳ぶのが面倒」と統合
- **トレードオフ**: 凝集度 ↔ Context Switch コスト
- **関連**: 凝集度 / Shotgun Surgery / Locality of Reference

### Consistency
- **読み**: コンシステンシー
- **定義**: 一貫性  命名・構造・パターンを揃えると Schema 適用が効き、新箇所でも既知の負荷で読める
- **使うとき**: {命名規則 / エラー処理パターン / レイヤ構造} の不整合を **統一** すると **新規参入者の学習負荷** が下がる。
- **適用すべきケース**:
  - チーム/プロジェクト全体のスタイル
  - 似た役割のクラスの構造
- **適用すべきでないケース**: 一貫性を理由に古い悪パターンを増殖
- **トレードオフ**: 統一の改修コスト ↔ 一貫性の利益
- **関連**: Schema / Ubiquitous Language

---

## トレードオフ・判断軸

### 認知負荷 vs 表現の正確性
- **定義**: 簡潔にしすぎると正確さが落ち、正確に書きすぎると認知負荷が上がる
- **使うとき**: **読み手の前提知識**に合わせて精度を調整。全員向けには簡潔 + 詳細リンク、専門家向けには高密度。
- **判断基準**: 読み手層 / 誤解されたら困るリスク / 後で詳細にアクセスできるか
- **関連**: Expert vs Novice Audience

### 抽象化 vs 学習コスト
- **定義**: 良い抽象化は熟練者の負荷を下げるが、新規参入者は抽象 + 具象の両方を学ぶ必要があり初期負荷が上がる
- **使うとき**: **新規参入頻度**を考慮。滅多に人が来ない / 全員熟練ならガッツリ抽象、出入りが多ければ薄い抽象。
- **判断基準**: チーム構成 / 抽象の自明性 / ドキュメント整備状況
- **関連**: Levels of Indirection / YAGNI
### DRY vs 局所性
- **定義**: DRY で共通化すると 1 箇所で済むが、読み手は別ファイルにジャンプして全貌を理解する Context Switch コストを払う
- **使うとき**: **変更頻度が高い箇所**は DRY 優先。**読み下し頻度が高い箇所**は局所性優先。
- **判断基準**: 変更 vs 読みの頻度比 / コピー 3 回以下なら局所性 (Rule of Three の逆適用)
- **コード例**:
  ```php
  // 過剰 DRY: 1〜2 箇所しか使わないのに共通化  別ファイルにジャンプしないと挙動が分からない
  // app/Shared/Formatter.php
  function formatTotalLabel(int $amount): string {
      return '合計: ¥' . number_format($amount);
  }
  // app/Controllers/OrderController.php
  class OrderController {
      public function show(int $id): View {
          $label = formatTotalLabel($order->total); // Formatter.php を開かないと挙動不明
          return view('order.show', compact('label'));
      }
  }

  // 局所性優先: その場で完結  Context Switch ゼロで読み下せる
  class OrderController {
      public function show(int $id): View {
          $label = '合計: ¥' . number_format($order->total);
          return view('order.show', compact('label'));
      }
  }
  // ※ 3 箇所以上で同じ整形が必要になった時点で DRY 化する (Rule of Three)
  ```
- **関連**: DRY / Context Switch / Locality of Reference

### Expert vs Novice Audience
- **読み**: エキスパート バーサス ノヴィス オーディエンス
- **定義**: 想定読み手のレベル  同じ内容でも、熟練者と初心者では「適切な認知負荷」が違う  熟練者は密度を求め、初心者は分解を求める
- **使うとき**: **想定読み手**を冒頭に明示 (例: 「Web 開発経験 1 年以上向け」)。期待値を揃えると **「これは私向けか？」判定コスト** が下がる。
- **判断基準**: ドキュメントなら明示、UI なら難易度モード切替
- **関連**: Schema / 認知負荷 vs 正確性
