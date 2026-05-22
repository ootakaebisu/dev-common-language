# 期待される指摘 — 02-feature-envy-input.php

## TL;DR

- 検出した指摘: 🔴 高: 0 / 🟡 中: 2 / 🟢 低: 0
- 推奨アクション: Anemic Domain Model 解消 → Customer に振る舞いを移譲する別 PR を起こす
- 主要な要注意箇所: Customer (貧血モデル), OrderService の各メソッド (Feature Envy)

## 指摘一覧

### 🟡 中 Anemic Domain Model — `Customer` クラス全体

**4 点セット**: **Anemic Domain Model** の観点から、**Customer がデータ入れ物 (public プロパティのみ) で、業務ルールが OrderService に流出している現状** を **Customer に振る舞いを移譲し、Service は薄い調整役にする** と、**ドメイン表現力 (Customer 単体で「何ができるか」が分かる) と Tell Don't Ask 適合度** が改善される

**トレードオフ**: 振る舞い集約 ↔ クラスの肥大化  → Aggregate 内で凝集すべき業務ルールに限定すれば肥大化しにくい

**今すぐ直すか / 後でいいか**: 別 PR  → 複数メソッドの移動を伴うため、本修正単独で 1 PR とする

**参照**: [vocab-ddd.md の Anemic Domain Model](../vocab-ddd.md)

---

### 🟡 中 Feature Envy — `OrderService::discountAmount`, `canPlaceOrder`, `mailingLabel`

**4 点セット**: **Feature Envy** の観点から、**OrderService の 3 メソッドが Customer の内部 (memberRank / frozen / firstName / lastName / zipCode / address) を多用して計算を組み立てている現状** を **Move Method で振る舞いを Customer 側に移動** すると、**Customer の凝集度と Tell Don't Ask 適合度** が改善される

**トレードオフ**: 振る舞い集約 ↔ Aggregate 境界  → 「Customer 単体で完結する操作は Customer に置く / 複数 Aggregate 跨ぎは Domain Service」が判定基準  本ケースはすべて Customer 単体で完結する

**移動後の姿**:

```php
class Customer {
    public function discountRate(): float {
        return match ($this->memberRank) {
            'gold'   => 0.15,
            'silver' => 0.05,
            default  => 0.0,
        };
    }
    public function canPlaceOrder(int $amount): bool {
        if ($this->frozen) return false;
        if ($this->memberRank === 'none' && $amount > 100000) return false;
        return true;
    }
    public function mailingLabel(): string {
        return "{$this->firstName} {$this->lastName}\n{$this->zipCode} {$this->address}";
    }
}
class OrderService {
    public function discountAmount(Customer $customer, int $subtotal): int {
        return (int) round($subtotal * $customer->discountRate());
    }
}
```

**今すぐ直すか / 後でいいか**: 別 PR (上の Anemic Domain Model 解消とセット)

**参照**: [vocab-code-quality-refactoring.md の Feature Envy / Move Method](../vocab-code-quality-refactoring.md), [vocab-design-principles.md の Tell Don't Ask](../vocab-design-principles.md)

## 検出したが指摘しなかったもの

| 候補語彙 | 対象 | 適用すべきでない理由 |
|---|---|---|
| Primitive Obsession | `memberRank` が `string` | 取り得る値が固定の列挙体なら enum 化が推奨だが、本指摘より Anemic 解消が先  Anemic 解消の PR で `MemberRank` enum 導入もセットにする方が自然 |

## 全体トレードオフ

Anemic Domain Model と Feature Envy は **同じ問題の 2 つの側面**  別々に直すのではなく「Customer に振る舞いを集約する」1 つの PR で同時解消するのが筋がいい
