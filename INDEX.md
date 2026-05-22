# 逆引きインデックス

「こういうコード / 状況を見たときに、どの語彙で表現すべきか」の逆引き集

ファイル名は省略形 (`vocab-` プレフィックス略):
- **design** = [vocab-design-principles.md](vocab-design-principles.md)
- **code-quality** = [vocab-code-quality-refactoring.md](vocab-code-quality-refactoring.md)
- **cog** = [vocab-cognitive-load.md](vocab-cognitive-load.md)
- **ddd** = [vocab-ddd.md](vocab-ddd.md)

より広いカバー範囲 (アーキテクチャ / テスト容易性 / 判断軸 / コミュニケーション) は [`final-vision` ブランチ](https://github.com/ootakaebisu/dev-common-language/tree/final-vision) の `INDEX.md` を参照

---

## A. コード構造のサイン

| 症状 / コードパターン | 候補語彙 |
|---|---|
| メソッドが 20 行を超えている / スクロールが必要 | Long Method (code-quality) / Extract Method (code-quality) / SRP (design) |
| 引数が 4 個以上 | Long Parameter List (code-quality) / Introduce Parameter Object (code-quality) / Data Clump (code-quality) |
| 同じ変数群が複数メソッドで一緒に渡される (例: `start, end, tz`) | Data Clump (code-quality) / Value Object (ddd) |
| `if/for/try` のネストが 3 段以上 | ネストの深さ (cog) / Levels of Indirection (cog) |
| 1 クラスが 500 行以上 / 何でもやってる | God Class (code-quality) / Extract Class (code-quality) / SRP (design) |
| 同じ型コードの switch が 3 箇所以上にコピペ | Switch Statements (code-quality) / Replace Conditional with Polymorphism (code-quality) / OCP (design) |
| 1 つの変更で 5 ファイル以上を触る | Shotgun Surgery (code-quality) |
| 同じクラスが複数の理由で変更される | Divergent Change (code-quality) / SRP (design) |
| 業務概念が `string` / `int` のプリミティブで持ち回されている | Primitive Obsession (code-quality) / Replace Primitive with Object (code-quality) / Value Object (ddd) |
| 「いつか使うかも」抽象 / 使われない引数・拡張点 | Speculative Generality (code-quality) / YAGNI (design) |
| 同じ知識 (税率・ルール等) が複数箇所に重複 | DRY (design) |
| 過剰な汎用化 / 設定可能化 | KISS (design) / YAGNI (design) |
| 親型に対し `instanceof` で分岐 / 子が `NotImplementedException` | LSP (design) |
| 1 interface に 5 メソッド以上、利用者ごとに使うメソッドが偏ってる | ISP (design) |
| クラス内で `new SmtpMailer()` / `DB::table(...)` / `time()` を直接呼んでる | DIP (design) |
| 1 ファイル内に独立した複数責務が同居 / 他クラスの内部実装に触れている | 凝集度・結合度 (design) |

## B. 振る舞いの置き場所のサイン

| 症状 | 候補語彙 |
|---|---|
| あるメソッドが他クラスの getter を多用してる | Feature Envy (code-quality) / Move Method (code-quality) / Tell Don't Ask (design) |
| 呼び出し側が状態を取り出して `if` で判定 | Tell Don't Ask (design) / Anemic Domain Model (ddd) |
| Entity が getter / setter しか持たない | Anemic Domain Model (ddd) / Tell Don't Ask (design) |
| 自分の直接の友達を超えて孫オブジェクトを触る (`a.getB().getC().doSomething()`) | Law of Demeter (design) |
| 2 つの Aggregate を跨ぐ調整ロジックが片方の Entity に書かれている | Domain Service (ddd) |
| 業務ルールが Application Service に流れて Entity が貧血化 | Anemic Domain Model (ddd) / Application Service (ddd) |
| getter / find 系メソッドが裏で書き込みを行っている | CQS (design) |
| Controller が業務判断 + 永続化 + 整形を全部書いてる | SRP (design) |

## C. 認知負荷のサイン (コード表現)

| 症状 | 候補語彙 |
|---|---|
| 名前から振る舞いが予測できない (`getUserList` が実は active のみ等) | 命名の予測可能性 (cog) / Schema (cog) |
| ラッパーが 3 段以上に重なっていて実装まで辿れない | Levels of Indirection (cog) / KISS (design) |
| 状態 (フラグ・表示判定) が Controller / Service / View に分散 | 状態の散在 vs 局在 (cog) |
| 修正のために多数のファイルを開く必要がある | Context Switch (cog) / 凝集度 (design) / Shotgun Surgery (code-quality) |
| 命名規則 / エラー処理 / レイヤ構造が箇所ごとにバラバラ | Consistency (cog) |
| AI への指示が「綺麗にして」レベルで止まる | 4 点セット表現 (各 vocab の「使うとき」) |
| 人間と AI で「読みやすさ」の感覚がズレる | Working Memory (cog) / Chunking (cog) |
| 1 ステップで覚えるべき情報が 4 個を超える | Working Memory (cog) / Chunking (cog) |
| DRY 過剰で別ファイルにジャンプしないと挙動が分からない | DRY vs 局所性 (cog) / DRY (design) |
| 読み手の前提知識と発信内容のレベルが合わない (簡潔すぎ / 正確すぎ) | 認知負荷 vs 表現の正確性 (cog) / Expert vs Novice Audience (cog) |
| 抽象化を入れたら新規参入者が読めなくなった | 抽象化 vs 学習コスト (cog) / Levels of Indirection (cog) |
| 想定読み手 (新人向け / 熟練者向け) が明示されていないドキュメント | Expert vs Novice Audience (cog) |

## D. 業務ロジック表現のサイン

| 症状 | 候補語彙 |
|---|---|
| 業務側「A」/ コード `b` / 画面「C」のように呼称がバラバラ | Ubiquitous Language (ddd) |
| 同じ語 (`Customer` 等) が複数文脈で違う意味で使われる | Bounded Context (ddd) / Context Map (ddd) |
| 外部 API / レガシー DB の語彙が Domain に漏れてる | Anti-Corruption Layer / ACL (ddd) |
| ID で同一性が決まるか、属性で同一性が決まるかが曖昧 | Entity (ddd) / Value Object (ddd) |
| 子 Entity を子用 Repository から直接書き換えて不変条件が壊れる | Aggregate / Aggregate Root (ddd) |
| 業務イベントから複数の副作用 (通知 / 集計 / 連携) が発生する | Domain Event (ddd) |
| コンストラクタが肥大化 / 不変条件チェックが多い | Factory (ddd) |
| Application Service が業務判断を抱えて肥大化 | Anemic Domain Model (ddd) / Application Service (ddd) |
