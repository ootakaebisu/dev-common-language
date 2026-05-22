# 逆引きインデックス

「こういうコード / 状況を見たときに、どの語彙で表現すべきか」の逆引き集

ファイル名は省略形 (`vocab-` プレフィックス略):
- **code-quality** = [vocab-code-quality-refactoring.md](vocab-code-quality-refactoring.md)
- **design** = [vocab-design-principles.md](vocab-design-principles.md)
- **arch** = [vocab-architecture.md](vocab-architecture.md)
- **ddd** = [vocab-ddd.md](vocab-ddd.md)
- **test** = [vocab-testability.md](vocab-testability.md)
- **cog** = [vocab-cognitive-load.md](vocab-cognitive-load.md) (コード領域)
- **comm** = [vocab-communication.md](vocab-communication.md) (UI / 文書 / 会話)
- **judge** = [vocab-judgment-tradeoffs.md](vocab-judgment-tradeoffs.md)

---

## A. コード構造のサイン

| 症状 / コードパターン | 候補語彙 |
|---|---|
| メソッドが 20 行を超えている / スクロールが必要 | Long Method, Extract Method (code-quality) / SRP (design) |
| 引数が 4 個以上 | Long Parameter List, Introduce Parameter Object (code-quality) / Data Clump (code-quality) |
| 同じ変数群が複数メソッドで一緒に渡される (例: `start, end, tz`) | Data Clump (code-quality) / Value Object (ddd) |
| `if/for/try` のネストが 3 段以上 | ネストの深さ, Levels of Indirection (cog) |
| 1 クラスが 500 行以上 / 何でもやってる | God Class, Extract Class (code-quality) / SRP (design) |
| 同じ型コードの switch が 3 箇所以上にコピペ | Switch Statements, Replace Conditional with Polymorphism (code-quality) / OCP (design) |
| 1 つの変更で 5 ファイル以上を触る | Shotgun Surgery (code-quality) |
| 同じクラスが複数の理由で変更される | Divergent Change (code-quality) / SRP (design) |
| 業務概念が `string` / `int` のプリミティブで持ち回されている | Primitive Obsession, Replace Primitive with Object (code-quality) / Value Object (ddd) |
| 「いつか使うかも」抽象 / 使われない引数・拡張点 | Speculative Generality (code-quality) / YAGNI (design / judge) |
| 同じ知識 (税率・ルール等) が複数箇所に重複 | DRY (design) |
| 過剰な汎用化 / 設定可能化 | KISS (design) / YAGNI (design) |
| 親型に対し `instanceof` で分岐 / 子が `NotImplementedException` | LSP (design) |
| 1 interface に 5 メソッド以上、利用者ごとに使うメソッドが偏ってる | ISP (design) |

## B. 振る舞いの置き場所のサイン

| 症状 | 候補語彙 |
|---|---|
| あるメソッドが他クラスの getter を多用してる | Feature Envy, Move Method (code-quality) / Tell Don't Ask (design) |
| 呼び出し側が状態を取り出して `if` で判定 | Tell Don't Ask (design) / Anemic Domain Model (ddd) |
| Entity が getter / setter しか持たない | Anemic Domain Model (ddd) / Tell Don't Ask (design) |
| 自分の直接の友達を超えて孫オブジェクトを触る (`a.getB().getC().doSomething()`) | Law of Demeter (design) |
| 2 つの Aggregate を跨ぐ調整ロジックが片方の Entity に書かれている | Domain Service (ddd) |
| Controller が業務判断 + 永続化 + 整形を全部書いてる | レイヤー責務 (arch) / SRP (design) |
| 業務ルールが Application Service に流れて Entity が貧血化 | Anemic Domain Model (ddd) / Application Service (ddd) |
| getter / find 系メソッドが裏で書き込みを行っている | CQS (design) |

## C. 依存・テストのサイン

| 症状 | 候補語彙 |
|---|---|
| テストが書けない / 遅い / flaky | Testability (test) / Seam (test) |
| クラス内で `new SmtpMailer()` / `DB::table(...)` / `time()` を直接呼んでる | Hard-coded Dependency, DI (test) / DIP (design) |
| Domain Entity が ORM (Eloquent 等) を直接継承 | 依存方向, 依存方向 (arch) / DIP (design) |
| UseCase が具象 Mailer / 具象 Repository を `new` してる | DIP (design) / DI (test) |
| Test Double を全部「モック」と呼んでる | Dummy / Fake / Stub / Mock / Spy (test) |
| 業務ロジック内に I/O (DB / HTTP / 時刻 / 乱数) が散在している | 副作用境界 (test) / Pure Function (test) |
| レガシーコードに変更を加える前の安全網が必要 | Characterization Test (test) / Seam (test) |
| `interface` を 1 実装しかないのに切ってる | DIP の過剰適用 (design) |
| 1 つの interface に複数役割が同居している (CRUD 全部入り等) | ISP (design) |

## D. 業務ロジック表現のサイン

| 症状 | 候補語彙 |
|---|---|
| 業務側「A」/ コード `b` / 画面「C」のように呼称がバラバラ | Ubiquitous Language (ddd) |
| 同じ語 (`Customer` 等) が複数文脈で違う意味で使われる | Bounded Context (ddd) / Context Map (ddd) |
| 外部 API / レガシー DB の語彙が Domain に漏れてる | Anti-Corruption Layer / ACL (ddd) / Boundary (arch) |
| ID で同一性が決まるか、属性で同一性が決まるかが曖昧 | Entity / Value Object (ddd) |
| 子 Entity を子用 Repository から直接書き換えて不変条件が壊れる | Aggregate / Aggregate Root (ddd) |
| 業務イベントから複数の副作用 (通知 / 集計 / 連携) が発生する | Domain Event (ddd) |
| コンストラクタが肥大化 / 不変条件チェックが多い | Factory (ddd) |
| DB Entity が API レスポンスまで貫通している | Boundary, DTO (arch) |

## E. 認知負荷のサイン (コード表現)

| 症状 | 候補語彙 |
|---|---|
| 名前から振る舞いが予測できない (`getUserList` が実は active のみ等) | 命名の予測可能性 (cog) / Schema (cog) |
| ラッパーが 3 段以上に重なっていて実装まで辿れない | Levels of Indirection (cog) / KISS (design) |
| 状態 (フラグ・表示判定) が Controller / Service / View に分散 | 状態の散在 vs 局在 (cog) |
| 修正のために多数のファイルを開く必要がある | Context Switch (cog) / 凝集度 (design) / Shotgun Surgery (code-quality) |
| 命名規則 / エラー処理 / レイヤ構造が箇所ごとにバラバラ | Consistency (cog) / Term Consistency (cog) |

## F. アーキ・レイヤーのサイン

| 症状 | 候補語彙 |
|---|---|
| Controller が DB / 外部 API を直接叩いてる | レイヤー責務, 依存方向 (arch) |
| 同じシナリオが Web / CLI / Batch から呼ばれる | Use Case (arch) |
| UseCase 内で表示文字列 / 通貨記号 / 日時フォーマットを作ってる | Presenter / ViewModel (arch) |
| ORM Entity を JSON でそのまま返してる | DTO, Boundary (arch) |
| 外部システム連携が複数 / テストで切りたい | Hexagonal Architecture (arch) / ACL (ddd) |
| 業務ロジックが FW / DB / UI を知っている | 依存方向, Clean Architecture (arch) / DIP (design) |

## G. 議論・判断のサイン

| 症状 | 候補語彙 |
|---|---|
| 「ここキャッシュ入れた方が速い」と計測前に主張している | Premature Optimization (judge) |
| 「いつか使うから」抽象を入れたがる | YAGNI (judge / design) / Speculative Generality (code-quality) |
| 議論時間の大半が命名・色・インデント等の些末に費やされる | Bikeshedding (judge) |
| 一度決めると戻せない判断と、いつでも戻せる判断が同じ重みで議論されてる | Two-way door / One-way door (judge) / Reversible / Irreversible (judge) |
| 「全部リファクタしたい」「全テスト書きたい」と全体最適を主張 | Pareto / 80-20 (judge) / Hot Path / Cold Path (judge) |
| 動くコードを目の前に「もっと綺麗に」と書き直し始める | Make it work → right → fast (judge) / KISS (design) |
| 「直したいけど時間ない」が口癖 | Tech Debt (judge) / Boy Scout Rule (judge) |
| 採用するか自作するか議論が泥沼化 | Build vs Buy (judge) |
| 内部実装の変更で利用者の振る舞いが壊れた | Hyrum's Law (judge) |
| 慣習に従ってるだけで理由を問えない | Cargo Cult Programming (judge) |
| 自分の主張に有利な情報だけ集めている | Confirmation Bias (judge) |
| チーム構造とアーキ構造がズレている | Conway's Law (judge) |

## H. 記述・伝達のサイン (文書・会話・UI)

| 症状 | 候補語彙 |
|---|---|
| 長文ドキュメントで結論まで遠い | TL;DR / BLUF / Pyramid Principle (comm) |
| 段落に複数主張が混ざってる | One Idea per Paragraph (comm) |
| 1 画面に操作ボタンが 10 個以上 | Hick's Law (comm) / Working Memory (cog) |
| 主要ボタンが小さい / 遠い | Fitts's Law (comm) |
| 配色が 5 色を超えている | Color Palette Limit (comm) / Selective Emphasis (comm) |
| 関連する要素がバラバラに配置されている | Gestalt Principles (comm) / Whitespace (comm) |
| 短時間で要点を伝えたい (朝会 / Slack 等) | PREP / BLUF / Rule of Three (comm) |
