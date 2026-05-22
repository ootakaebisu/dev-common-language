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

## 収録カテゴリ (4 主要分野)

| ファイル | 領域 |
|---|---|
| [vocab-design-principles.md](vocab-design-principles.md) | **設計原則** — SOLID (SRP / OCP / LSP / ISP / DIP) + SOLID 以外の頻出原則 (DRY / KISS / YAGNI / 凝集度・結合度 / Tell Don't Ask / CQS / Law of Demeter) |
| [vocab-code-quality-refactoring.md](vocab-code-quality-refactoring.md) | **コード品質 + リファクタリング** — コードの臭い (Long Method / Feature Envy / God Class 等) + リファクタリング手法 (Extract Method / Move Method / Replace Conditional with Polymorphism 等) |
| [vocab-cognitive-load.md](vocab-cognitive-load.md) | **認知負荷** — コード領域 (命名の予測可能性 / Levels of Indirection / ネストの深さ / 状態の散在 vs 局在 / Context Switch / Consistency 等)  「読みづらい」「ネスト深い」を感覚で語らず具体化するための語彙 |
| [vocab-ddd.md](vocab-ddd.md) | **DDD** — 戦略 (Ubiquitous Language / Bounded Context / Context Map / ACL) + 戦術 (Entity / Value Object / Aggregate / Domain Service / Repository / Factory / Domain Event) + アンチパターン |

機械的な原則の振りかざしを避け、状況に応じた「便益 vs コスト」判断ができるよう、各エントリには「**適用すべきでないケース**」と「**トレードオフ**」を必ず含める

「こういうコード見たけど何て言えばいい？」の **逆引き** は [INDEX.md](INDEX.md) を参照

## なぜこの 4 分野か

### 設計原則 / コード品質+リファクタリング

「分割すべきか」「臭うか」「直すべきか」のような日常レビューで頻出する判断を言語化できるため

### DDD

業務ルールが複雑な領域で、業務ロジックをコード上のどこに置くか・どう境界を引くかの判断軸が必要になるため

### 認知負荷

**ソフトウェアエンジニアリング直接の語彙ではない** が、重視する理由:

- **AI 協業で人間 ↔ AI の差が最も出やすい領域**  AI は実質無制限のワーキングメモリを持つが、人間は 4±1 チャンク  この非対称性が「AI が出す出力を人間がレビューできない」事故を生む
- **人間間コミュニケーションで暗黙的・すれ違いになりやすい**  「読みづらい」「ややこしい」を感覚で語る限り議論は空中戦  認知心理学の確立した語彙 (Working Memory / Chunking / Schema) で具体に降ろせる
- 認知負荷を意識した設計は「ワーキングメモリが小さい人を基準にする = 下限保証」  上振れ最適化より波及効果が大きい

## 使い方

**人との対話**:
1. 違和感を覚えたコード / 状況を [INDEX.md](INDEX.md) の症状表 (A〜D) で探す
2. 候補語彙のリンク先 `vocab-*.md` を読む  特に **「適用すべきでないケース」を必ず確認** (誤検出フィルタ)
3. 「使うとき」の 4 点セット表現で議論を言語化する

**AI との対話**:
1. 対象コード / 状況からマッチする症状を抽出
2. [INDEX.md](INDEX.md) で候補語彙を引き、`vocab-*.md` を読む
3. 「適用すべきでないケース」と照合 → 該当する場合は指摘しない
4. 4 点セット表現 + トレードオフ + 「今直すか / 後でいいか」付きで出力

## 最終ゴールイメージ (final-vision ブランチで先行公開)

本 main ブランチは 4 主要分野の語彙集に絞った最小構成

将来的に追加する補助ファイル (逆引きインデックス / レビュー出力テンプレート / 概念マップ / 評価ケース / アーキテクチャ・判断軸・テスト容易性・コミュニケーションの追加 4 分野) は [`final-vision` ブランチ](https://github.com/ootakaebisu/dev-common-language/tree/final-vision) で先行公開している

最終形のレビュー Skill 連携や [INDEX.md](https://github.com/ootakaebisu/dev-common-language/blob/final-vision/INDEX.md) / [OUTPUT-FORMAT.md](https://github.com/ootakaebisu/dev-common-language/blob/final-vision/OUTPUT-FORMAT.md) / [examples/](https://github.com/ootakaebisu/dev-common-language/tree/final-vision/examples) の使い方はそちらを参照

## ライセンス

個人ナレッジの公開スナップショット  自由に参照・引用してよい
