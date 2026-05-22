# 期待される指摘 — 01-long-method-input.php

## TL;DR

- 検出した指摘: 🔴 高: 1 / 🟡 中: 2 / 🟢 低: 0
- 推奨アクション: 🔴 を本 PR で対応、🟡 は別 PR で UseCase / DI 整備とセット
- 主要な要注意箇所: Long Method (OrderController::place), レイヤー責務違反, Hard-coded Dependency (SmtpMailer)

## 指摘一覧

### 🔴 高 Long Method — `OrderController::place` (約 60 行、4 責務同居)

**4 点セット**: **Long Method** の観点から、**バリデーション・料金計算・永続化・通知を同じメソッド内に書いている現状** を **Extract Method で 4 つの目的別 private メソッドに分割** すると、**メソッド名から意図が読める可読性とユニットテスト容易性** が改善される

**トレードオフ**: 短さ ↔ 過剰分割で全体像が見えない  → 「1 メソッド = 1 目的 + 1 抽象レベル」が基準

**今すぐ直すか / 後でいいか**: 即時 (本 PR で対応)  → このメソッドが本機能の中核で、レビュー読解負荷に直結

**参照**: [vocab-code-quality-refactoring.md の Long Method / Extract Method](../vocab-code-quality-refactoring.md)

---

### 🟡 中 レイヤー責務違反 — `OrderController::place`

**4 点セット**: **レイヤー責務** の観点から、**Controller が業務判断 (割引計算・税計算) と永続化 (DB 直接アクセス) を抱えている現状** を **UseCase に業務シナリオを切り出し、Repository に永続化を切り出す** と、**業務ロジックの再利用性 (CLI / Batch から呼べる) とテスト容易性** が改善される

**トレードオフ**: 一貫性 ↔ 複雑度に応じた柔軟性  → 単純 CRUD は Controller 直書きで十分だが、本ケースは業務判断が複数あるので UseCase 化が妥当

**今すぐ直すか / 後でいいか**: 別 PR  → Long Method 解消だけで本 PR は閉じ、UseCase / Repository 化は別 PR でアーキ整備として行う

**参照**: [vocab-architecture.md の Use Case / レイヤー責務](../vocab-architecture.md)

---

### 🟡 中 Hard-coded Dependency — `OrderController::place:63` (`new SmtpMailer()`)

**4 点セット**: **Hard-coded Dependency** の観点から、**Controller 内で `new SmtpMailer()` を直接生成している現状** を **Mailer interface を Domain 側に切って Constructor Injection で受け取る** と、**テスト時の Fake 差し替え可能性とユニットテストの実行速度** が改善される

**トレードオフ**: 単純さ ↔ テスト容易性  → 外部 I/O 依存は DI 推奨

**今すぐ直すか / 後でいいか**: 別 PR  → UseCase 化と合わせて行う方が一貫した依存設計になる

**参照**: [vocab-testability.md の Hard-coded Dependency / DI](../vocab-testability.md)

## 検出したが指摘しなかったもの

| 候補語彙 | 対象 | 適用すべきでない理由 |
|---|---|---|
| SRP | OrderController クラス全体 | クラスレベルでは「注文操作の入口」として責務がまとまっており、メソッドレベルの SRP 違反 (Long Method) として既に指摘済み  二重指摘を避ける |
| Primitive Obsession | `$email` が `string` で流通 | 本 PR のスコープは「注文確定の追加」  Email VO 化は別 PR で全エンドポイント横断で行う方が一貫性が高い |
| DRY | 税率 0.1 / 送料 500 / 割引率 0.15 のマジックナンバー | 価格ポリシーの集約は Long Method 解消 (calculateTotal メソッド抽出) で部分的に解消される  完全な集約 (TaxPolicy クラス化等) は別 PR |

## 全体トレードオフ

本 PR の最優先は **読解負荷の即時改善 (Long Method 解消)**  Extract Method による責務分割だけ本 PR で行い、UseCase / DI / VO 化は別 PR で「アーキ整備」として一括で進める方が PR 単位の凝集度が高い
