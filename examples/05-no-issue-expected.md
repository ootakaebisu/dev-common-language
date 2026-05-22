# 期待される指摘 — 05-no-issue-input.php

## TL;DR

- 検出した指摘: 🔴 高: 0 / 🟡 中: 0 / 🟢 低: 0
- 推奨アクション: 指摘なし  3 ケースすべて「適用すべきでないケース」に該当するため誤検出を排除

このファイルは **誤検出排除の検証ケース**  一見「臭い」が出ていそうな 3 パターンに対して、レビュー Skill が各語彙の「適用すべきでないケース」を正しく参照して指摘を出さないかを確認する

## 検出したが指摘しなかったもの (詳細)

| # | 候補語彙 | 対象 | 適用すべきでない理由 |
|---|---|---|---|
| A | Feature Envy | `OrderViewModel::build` が `Order` / `Customer` の getter を多用 | **ViewModel / Presenter / Serializer は「他クラスの値を組み合わせて表示」が本来の目的**  vocab-code-quality-refactoring.md の Feature Envy「適用すべきでないケース」に明示されている |
| B | Long Parameter List | `calculateGcd(int $a, $b, $c, $d)` の引数 4 個 | **意味的に独立な 4 引数を無理にまとめると凝集度が下がる**  vocab-code-quality-refactoring.md の Long Parameter List「適用すべきでないケース」に明示されている  Parameter Object 化はかえって過剰 |
| C | Long Method | `buildOrderQuery` が 30 行 | **直列の SQL 構築 / DSL 構築など分割すると逆に読みにくい箇所**  vocab-code-quality-refactoring.md の Long Method「適用すべきでないケース」に明示されている  Extract Method は不要 |

## 全体トレードオフ

各語彙の **「適用すべきでないケース」をしっかり読むことで、機械的な原則違反検出による誤指摘 (= 認知負荷の押し付け) を防げる**  本ケースは「指摘しない判断」そのものが Skill の品質指標になる

## このケースが Skill 評価で重要な理由

普通のコードレビュー AI は「行数が長い」「引数が多い」「getter 多用」だけを見て機械的に指摘を出してしまう  本リポジトリの語彙集は各エントリに「適用すべきでないケース」を明示することで、**指摘を出さない判断** を Skill に教えることができる  これがノイズの少ないレビューの核
