<?php

// レビュー対象: Customer (貧血モデル) と OrderService の関係

class Customer
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $zipCode;
    public string $address;
    public string $memberRank; // 'gold' | 'silver' | 'bronze' | 'none'
    public bool $frozen;
}

class OrderService
{
    public function discountAmount(Customer $customer, int $subtotal): int
    {
        $rate = 0;
        if ($customer->memberRank === 'gold') {
            $rate = 0.15;
        } elseif ($customer->memberRank === 'silver') {
            $rate = 0.05;
        }
        return (int) round($subtotal * $rate);
    }

    public function canPlaceOrder(Customer $customer, int $amount): bool
    {
        if ($customer->frozen) {
            return false;
        }
        if ($customer->memberRank === 'none' && $amount > 100000) {
            return false;
        }
        return true;
    }

    public function mailingLabel(Customer $customer): string
    {
        $name = $customer->firstName . ' ' . $customer->lastName;
        $addr = $customer->zipCode . ' ' . $customer->address;
        return "{$name}\n{$addr}";
    }
}
