# 期待される指摘 — 03-switch-input.php

## TL;DR

- 検出した指摘: 🟡 中: 1
- 推奨アクション: PaymentMethod interface に振り分ける Replace Conditional with Polymorphism
- 主要な要注意箇所: PaymentProcessor の 3 メソッドが同じ `$type` で switch している

## 指摘一覧

### 🟡 中 Switch Statements / OCP 違反 — `PaymentProcessor`

**4 点セット**: **Switch Statements** の観点から、**同じ `$type` に対する switch が 3 メソッドにコピペ的に存在し、新種別追加 (例: 暗号資産決済) のたびに 3 箇所触る現状** を **PaymentMethod interface + 各種別の実装クラスに分離 (Replace Conditional with Polymorphism)** すると、**新種別追加時の既存コード非侵襲性 (OCP 適合)** が改善される

**トレードオフ**: 拡張性 ↔ 抽象化コスト  → 「追加実績 2 回以上」が現実的閾値  本ケースは既に 4 種別 (card / bank / paypay / convenience) + 暗号資産が控えていると想定すれば妥当

**移動後の姿**:

```php
interface PaymentMethod {
    public function fee(int $amount): int;
    public function settlementDays(): int;
    public function isAvailable(Customer $customer): bool;
}
final class CardPayment implements PaymentMethod {
    public function fee(int $amount): int { return (int) round($amount * 0.036); }
    public function settlementDays(): int { return 3; }
    public function isAvailable(Customer $customer): bool { return $customer->hasVerifiedCard; }
}
// BankPayment / PayPayPayment / ConveniencePayment も同じ構造  新種別は新クラス 1 個追加で完結
```

**今すぐ直すか / 後でいいか**: 別 PR  → 既存呼び出し側の修正も伴うため独立 PR が安全

**参照**: [vocab-code-quality-refactoring.md の Switch Statements / Replace Conditional with Polymorphism](../vocab-code-quality-refactoring.md), [vocab-design-principles.md の OCP](../vocab-design-principles.md)

## 検出したが指摘しなかったもの

| 候補語彙 | 対象 | 適用すべきでない理由 |
|---|---|---|
| Primitive Obsession | `$type` が `string` | Replace Conditional with Polymorphism 適用時に自然に解消される (interface 型になるため)  単独指摘は不要 |
