<?php

// レビュー対象: 注文確定ハンドラ
// バリデーション + 料金計算 + 永続化 + 通知が 1 メソッドに同居している

class OrderController
{
    public function place(Request $req): Response
    {
        // バリデーション
        $items = $req->input('items');
        if (empty($items)) {
            return new Response(['error' => 'cart is empty'], 400);
        }
        foreach ($items as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                return new Response(['error' => 'invalid item'], 400);
            }
            if ($item['quantity'] <= 0) {
                return new Response(['error' => 'invalid quantity'], 400);
            }
        }
        $email = $req->input('customer_email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new Response(['error' => 'invalid email'], 400);
        }

        // 料金計算
        $subtotal = 0;
        foreach ($items as $item) {
            $product = DB::table('products')->find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }
        $tax = (int) round($subtotal * 0.1);
        $shipping = $subtotal >= 5000 ? 0 : 500;
        $customer = DB::table('customers')->where('email', $email)->first();
        $discount = 0;
        if ($customer && $customer->rank === 'gold') {
            $discount = (int) round($subtotal * 0.15);
        }
        $total = $subtotal + $tax + $shipping - $discount;

        // 永続化
        $orderId = DB::table('orders')->insertGetId([
            'customer_email' => $email,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ($items as $item) {
            DB::table('order_lines')->insert([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        // 通知
        $mailer = new SmtpMailer();
        $mailer->send($email, 'Order Confirmed', "Your order #{$orderId} has been placed. Total: ¥{$total}");

        return new Response(['order_id' => $orderId, 'total' => $total], 201);
    }
}
