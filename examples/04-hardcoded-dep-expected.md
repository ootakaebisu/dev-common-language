# 期待される指摘 — 04-hardcoded-dep-input.php

## TL;DR

- 検出した指摘: 🔴 高: 1 / 🟡 中: 1
- 推奨アクション: 副作用境界を整理し、整形ロジックを Pure Function 化  並行して DI 注入で I/O を差し替え可能に
- 主要な要注意箇所: 業務ロジックと I/O が同居していてユニットテストが書けない

## 指摘一覧

### 🔴 高 副作用境界違反 / Hard-coded Dependency — `DailyReportService::generate`

**4 点セット**: **副作用境界** の観点から、**業務ロジック内に I/O (時刻取得・DB・外部 API・ファイル書き出し) が散在し、整形ロジック単体のテストが書けない現状** を **整形ロジックを Pure Function に切り出し、I/O を Clock / Repository / ExchangeRateApi / FileWriter として DI 注入** すると、**業務ロジックのテスト容易性とアダプタ差し替え可能性** が改善される

**トレードオフ**: 単純さ ↔ テスト容易性 + 設定の柔軟性  → I/O が 4 種類同居している現状は明確に DI 投入の閾値を超えている

**移動後の姿**:

```php
// Pure Function (テストしやすい)
function buildDailyReport(string $date, array $orders, float $rate): array {
    $lines = [];
    $total = 0;
    foreach ($orders as $order) {
        $totalJpy = $order->totalUsd * $rate;
        $total += $totalJpy;
        $lines[] = "- Order #{$order->id}: ¥{$totalJpy}";
    }
    return ['report' => "Daily Report ({$date})\n" . implode("\n", $lines) . "\nTotal: ¥{$total}\n", 'total' => $total];
}

// 副作用境界は外周だけに集める
class DailyReportService {
    public function __construct(
        private Clock $clock,
        private OrderRepository $orders,
        private ExchangeRateApi $exchange,
        private FileWriter $writer,
    ) {}
    public function generate(): string {
        $today = $this->clock->today();
        $orders = $this->orders->findByDate($today);
        $rate = $this->exchange->currentRate('USD', 'JPY');
        $result = buildDailyReport($today, $orders, $rate);
        $this->writer->save("daily-{$today}.txt", $result['report']);
        return $result['report'];
    }
}
```

**今すぐ直すか / 後でいいか**: 即時 (テスト不能性は将来の改修ですべての変更にテストが書けない問題を引き起こす)

**参照**: [vocab-testability.md の 副作用境界 / Hard-coded Dependency / DI / Pure Function](../vocab-testability.md), [vocab-design-principles.md の DIP](../vocab-design-principles.md)

---

### 🟡 中 CQS 違反 — `DailyReportService::generate`

**4 点セット**: **CQS** の観点から、**`generate` が「レポート生成 (Query)」と「ファイル書き出し (Command)」を同居させている現状** を **`generate` は文字列を返す Query、`saveDailyReport` は Command として分離** すると、**呼び出し側の予測可能性とテスト容易性** が改善される

**トレードオフ**: 純粋さ ↔ 利便性 (1 回呼ぶだけで完結する利便性は失われる)  → 副作用境界の整理と同時に行えば自然に分離できる

**今すぐ直すか / 後でいいか**: 上の副作用境界整理とセットで即時対応

**参照**: [vocab-design-principles.md の CQS](../vocab-design-principles.md)

## 検出したが指摘しなかったもの

| 候補語彙 | 対象 | 適用すべきでない理由 |
|---|---|---|
| Primitive Obsession | `$today` が `string` (Y-m-d) | Clock interface 導入時に `Date` Value Object として扱えば自然に解消  単独指摘は不要 |
