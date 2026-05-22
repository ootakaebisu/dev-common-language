# examples/

レビュー Skill の評価ケース  Before コード + 期待される指摘 (Expected) のペア

## 構成

各ケースは 2 ファイル組:

- `*-input.{php,md}` — レビュー対象のコード or 状況
- `*-expected.md` — 本リポジトリの語彙を用いた期待される指摘 ([OUTPUT-FORMAT.md](../OUTPUT-FORMAT.md) 準拠)

Skill が Input を読んで Expected と同等の指摘を返せたら合格

## ケース一覧

| ケース | 入力 | 期待される指摘 | 主な検証ポイント |
|---|---|---|---|
| 01: Long Method + SRP 違反 | [01-long-method-input.php](01-long-method-input.php) | [01-long-method-expected.md](01-long-method-expected.md) | 複数の責務同居の検出、Extract Method の提案、4 点セット表現 |
| 02: Feature Envy + Anemic Domain | [02-feature-envy-input.php](02-feature-envy-input.php) | [02-feature-envy-expected.md](02-feature-envy-expected.md) | 他クラス getter 多用の検出、Move Method 提案、貧血モデル指摘 |
| 03: Switch Statements + OCP 違反 | [03-switch-input.php](03-switch-input.php) | [03-switch-expected.md](03-switch-expected.md) | 種別 switch のコピペ検出、Polymorphism 提案 |
| 04: Hard-coded Dependency | [04-hardcoded-dep-input.php](04-hardcoded-dep-input.php) | [04-hardcoded-dep-expected.md](04-hardcoded-dep-expected.md) | テスト不能性の検出、DI 提案、Seam 概念 |
| 05: 誤検出排除 (適用すべきでないケース) | [05-no-issue-input.php](05-no-issue-input.php) | [05-no-issue-expected.md](05-no-issue-expected.md) | 「適用すべきでないケース」に該当する例で誤検出を出さないことの検証 |

## 評価方法

```bash
# 将来 Skill が完成したら以下のような評価が可能:
# 1. Skill に Input を渡す
# 2. 出力を Expected と比較
# 3. 一致率を測定
```

現時点では人間が読み比べて品質を確認する  Skill 実装時に自動評価へ移行する
