# dev-common-language

開発の共通言語  人 / AI 問わず議論・合意形成で使う語彙集

## 背景

AI への指示・コードレビュー・自分の判断  これらの精度を語彙が大きく左右すると考え、共通言語集が必要であると判断した

## 目的

「なんとなく」を **「{原則} の観点から {現状} を {提案} にすると {改善される性質} が改善される」** の 4 点セット表現に変換する

## 想定する読者

経験則や感覚として「これ良くない気がする」「読みづらい」「臭う」と判定はできるが、それを **論理的に言語化するための語彙を補強したい人** が主対象

例えば:
- コードレビューで「なんかこの設計微妙」と感じる時に、相手に伝える具体的な言葉を補強したい
- AI に指示出しするとき「もっと綺麗にして」ではなく、より精度の高い指示を出したい
- 同僚への提案で「なんとなく変えた方がいい」「直感的に取り入れたい」レベルから抜けて、具体的な議論に進めたい

すでに SOLID / DDD / リファクタリング / 認知心理学を体系的に学んだ人には **再確認用** や **メンバーとの共通の参照先** として機能する

## 4 主要分野の選定軸 (頻度 + 業務性 + 暗黙性)

`vocab-*.md` 8 ファイルのうち、本リポジトリの原点となる **4 主要分野** (設計原則 / コード品質+リファクタ / 認知負荷 / DDD) は以下の軸で選定された:

- **設計原則 / コード品質+リファクタ**: 「分割すべきか」「臭うか」「直すべきか」のような日常レビューで頻出する判断を言語化できるため
- **DDD**: 業務ルールが複雑な領域で、業務ロジックをコード上のどこに置くか・どう境界を引くかの判断軸が必要になるため
- **認知負荷**: ソフトウェアエンジニアリング直接の語彙ではないが、AI 協業での人間 ↔ AI 差 (実質無制限のワーキングメモリ vs 4±1 チャンク)、人間間コミュニケーションの「読みづらい」「ややこしい」を具体化できるため

残り 4 分野 (アーキテクチャ / 判断軸・トレードオフ / テスト容易性 / コミュニケーション) は本ブランチ (`final-vision`) で拡張版として公開

## クイックスタート

| 何をしたい | どこを見る |
|---|---|
| 「こういうコードを見たけど何て言えばいい？」 | [INDEX.md](INDEX.md) — 症状 → 候補語彙の逆引き |
| 「この語彙の意味と使い方を知りたい」 | 該当の `vocab-*.md` (下の一覧) |
| 「語彙集を更新したい / FB したい」 | [WRITING-RULES.md](WRITING-RULES.md) — 議論姿勢 + 記述ルール |
| 「レビュー指摘を本リポジトリの語彙で書きたい」 | [OUTPUT-FORMAT.md](OUTPUT-FORMAT.md) — レビュー出力テンプレート |
| 「全体像を俯瞰したい」 | [CONCEPT-GRAPH.md](CONCEPT-GRAPH.md) — 語彙のクラスタ図 |
| 「Skill 化するときの評価ケースが見たい」 | [examples/](examples/) — Before コード + 期待される指摘 |

## カテゴリ

| ファイル | 領域 | 項目数 |
|---|---|---:|
| [vocab-design-principles.md](vocab-design-principles.md) | 設計原則 (SOLID / DRY / KISS / YAGNI 等) | 12 |
| [vocab-code-quality-refactoring.md](vocab-code-quality-refactoring.md) | コード品質 + リファクタリング (命名 / 早期 return / コードの臭い / Fowler カタログ) | 16 |
| [vocab-architecture.md](vocab-architecture.md) | アーキテクチャ (Clean / Layered / Hexagonal / 依存方向) | 9 |
| [vocab-ddd.md](vocab-ddd.md) | DDD (Entity / VO / Aggregate / Bounded Context / ACL) | 13 |
| [vocab-testability.md](vocab-testability.md) | テスト容易性・依存設計 | 16 |
| [vocab-judgment-tradeoffs.md](vocab-judgment-tradeoffs.md) | 判断軸・トレードオフ・メタ (ノイズ排除の本丸) | 20 |
| [vocab-cognitive-load.md](vocab-cognitive-load.md) | 認知負荷 (コード領域の認知負荷) | 15 |
| [vocab-communication.md](vocab-communication.md) | コミュニケーション (UI / 文書 / 会話・プレゼン) | 15 |
| **合計** | | **116** |

機械的な原則の振りかざしを避け、状況に応じた「便益 vs コスト」判断ができるよう、各エントリには「**適用すべきでないケース**」と「**トレードオフ**」を必ず含める  詳細記述ルールは [WRITING-RULES.md](WRITING-RULES.md) の F1-F9 を参照

## 使い方

**人との対話**:
1. 違和感を覚えたコード / 状況を [INDEX.md](INDEX.md) の症状表 (A〜H) で探す
2. 候補語彙のリンク先 `vocab-*.md` を読む  特に **「適用すべきでないケース」を必ず確認** (誤検出フィルタ)
3. 「使うとき」の 4 点セット表現で議論を言語化する

**AI との対話 / Skill 連携**:
1. メタデータ (frontmatter) は [INDEX.md](INDEX.md) と [WRITING-RULES.md](WRITING-RULES.md) でロード
2. 対象コードから症状を抽出 → [INDEX.md](INDEX.md) で候補語彙を引き、`vocab-*.md` を progressive disclosure で読む
3. 「適用すべきでないケース」と照合 → 該当する場合は指摘しない
4. [OUTPUT-FORMAT.md](OUTPUT-FORMAT.md) の 5 要素 (語彙名 / 4 点セット / トレードオフ / 即時 or 後で / 参照) で出力

## ライセンス

個人ナレッジの公開スナップショット  自由に参照・引用してよい
