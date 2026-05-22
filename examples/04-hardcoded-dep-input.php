<?php

// レビュー対象: テストが書けないクラス  外部 I/O が直接呼ばれている

class DailyReportService
{
    public function generate(): string
    {
        // 時刻取得が直接呼ばれている
        $today = date('Y-m-d');

        // DB 接続が直接呼ばれている (DI されてない)
        $orders = DB::table('orders')
            ->whereDate('created_at', $today)
            ->get();

        // 外部 API も直接呼ばれている
        $rate = file_get_contents('https://exchange.example.com/api/rate?from=USD&to=JPY');

        // 整形ロジック (本来テストしたい部分)
        $report = "Daily Report ({$today})\n";
        $total = 0;
        foreach ($orders as $order) {
            $totalJpy = $order->total_usd * (float) $rate;
            $total += $totalJpy;
            $report .= "- Order #{$order->id}: ¥{$totalJpy}\n";
        }
        $report .= "Total: ¥{$total}\n";

        // ファイル書き出しも直接呼ばれている
        file_put_contents("/var/reports/daily-{$today}.txt", $report);

        return $report;
    }
}
