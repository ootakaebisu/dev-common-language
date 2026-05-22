<?php

// レビュー対象: 種別ごとの switch が複数メソッドにコピペされている

class PaymentProcessor
{
    public function fee(string $type, int $amount): int
    {
        switch ($type) {
            case 'card':
                return (int) round($amount * 0.036);
            case 'bank':
                return 200;
            case 'paypay':
                return (int) round($amount * 0.025);
            case 'convenience':
                return 300;
        }
        throw new InvalidArgumentException("unknown payment type: {$type}");
    }

    public function settlementDays(string $type): int
    {
        switch ($type) {
            case 'card':
                return 3;
            case 'bank':
                return 1;
            case 'paypay':
                return 7;
            case 'convenience':
                return 5;
        }
        throw new InvalidArgumentException("unknown payment type: {$type}");
    }

    public function isAvailable(string $type, Customer $customer): bool
    {
        switch ($type) {
            case 'card':
                return $customer->hasVerifiedCard;
            case 'bank':
                return true;
            case 'paypay':
                return $customer->hasPayPayAccount;
            case 'convenience':
                return $customer->isAdult;
        }
        throw new InvalidArgumentException("unknown payment type: {$type}");
    }
}
