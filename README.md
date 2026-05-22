# dev-common-language

開発の共通言語  人 / AI 問わず議論・合意形成で使う語彙集

## 目的

「なんとなく」を **「{原則} の観点から {現状} を {提案} にすると {改善される性質} が改善される」** の 3 点セット表現に変換する  AI への指示・コードレビュー・自分の判断  すべての精度を語彙が決める

## 現状

リポジトリ初期化中  第一フェーズとして以下の 4 主要分野の語彙ファイルを取り込む

- [ ] **設計原則** — SOLID (SRP / OCP / LSP / ISP / DIP) + SOLID 以外の頻出原則 (DRY / KISS / YAGNI / 凝集度・結合度 / Tell Don't Ask / CQS / Law of Demeter)
- [ ] **コード品質 + リファクタリング** — コードの臭い (Long Method / Feature Envy / God Class 等) + リファクタリング手法 (Extract Method / Move Method / Replace Conditional with Polymorphism 等)
- [ ] **認知負荷** — コード領域 (命名の予測可能性 / Levels of Indirection / ネストの深さ / 状態の散在 vs 局在 / Context Switch / Consistency 等)  「読みづらい」「ネスト深い」を感覚で語らず具体に降ろすための語彙
- [ ] **DDD** — 戦略 (Ubiquitous Language / Bounded Context / Context Map / ACL) + 戦術 (Entity / Value Object / Aggregate / Domain Service / Repository / Factory / Domain Event) + アンチパターン

## 最終ゴールイメージ

将来的に追加する補助ファイル (逆引きインデックス / レビュー出力テンプレート / 概念マップ / 評価ケース / アーキテクチャ・判断軸・テスト容易性・コミュニケーションの追加 4 分野) は **[`final-vision`](https://github.com/ootakaebisu/dev-common-language/tree/final-vision) ブランチ** で先行公開している

最終形のレビュー Skill 連携や `INDEX.md` / `OUTPUT-FORMAT.md` / `examples/` の使い方はそちらを参照
