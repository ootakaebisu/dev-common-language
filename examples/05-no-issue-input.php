<?php

// レビュー対象: 一見「臭い」が出ていそうだが、実際は適用すべきでないケースに該当する例
// レビュー Skill がこのケースで誤検出を出さないかを検証する

// ケース A: ViewModel への詰め替えで他クラスの getter を多用しているが、これは Feature Envy ではない
//          (ViewModel / Presenter / Serializer は「他クラスの値を組み合わせて表示」が本来の目的)

class OrderViewModel
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerName,
        public readonly string $totalLabel,
        public readonly string $statusLabel,
    ) {}

    public static function build(Order $order, Customer $customer): self
    {
        // 他クラスの getter を多用するが、これは表示用詰め替えなので Feature Envy には該当しない
        return new self(
            orderId: $order->getId()->value,
            customerName: $customer->getFirstName() . ' ' . $customer->getLastName(),
            totalLabel: '¥' . number_format($order->getTotal()),
            statusLabel: self::translateStatus($order->getStatus()),
        );
    }

    private static function translateStatus(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Pending   => '受付中',
            OrderStatus::Confirmed => '確定',
            OrderStatus::Shipped   => '発送済',
            OrderStatus::Canceled  => 'キャンセル',
        };
    }
}

// ケース B: 引数 4 個だが、Long Parameter List ではない
//          (意味的に独立した 4 引数で、Parameter Object 化するとかえって凝集度が下がる)

function calculateGcd(int $a, int $b, int $c, int $d): int
{
    return gcd(gcd($a, $b), gcd($c, $d));
}

// ケース C: 1 メソッド 30 行だが、Long Method ではない
//          (直列の SQL 構築で、分割すると逆に読みにくくなる)

function buildOrderQuery(array $filters): string
{
    $sql = "SELECT o.id, o.total, o.status, c.name AS customer_name";
    $sql .= " FROM orders o";
    $sql .= " INNER JOIN customers c ON c.id = o.customer_id";
    $sql .= " LEFT JOIN order_lines ol ON ol.order_id = o.id";
    $sql .= " WHERE 1=1";

    if (!empty($filters['status'])) {
        $sql .= " AND o.status = :status";
    }
    if (!empty($filters['customer_id'])) {
        $sql .= " AND o.customer_id = :customer_id";
    }
    if (!empty($filters['from'])) {
        $sql .= " AND o.created_at >= :from";
    }
    if (!empty($filters['to'])) {
        $sql .= " AND o.created_at <= :to";
    }

    $sql .= " GROUP BY o.id, o.total, o.status, c.name";
    $sql .= " ORDER BY o.created_at DESC";

    return $sql;
}
