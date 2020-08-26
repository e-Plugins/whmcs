<?php
namespace Digiwallet\Packages\Transaction\Client\Payment;

/**
 * Class PaymentMethod
 * @package Digiwallet\Packages\Transaction\Client\PaymentMethod
 */
class Payment
{
    public const AFTERPAY = 'AFP';
    public const CREDIT_CARD = 'CRC';
    public const IDEAL = 'IDE';
    public const BANCONTACT = 'MRC';
    public const PAY_SAFE_CARD = 'PSC';
    public const PAY_PAL = 'PYP';
    public const SOFORT = 'SOF';
    public const GIROPAY = 'GIP';
    public const EPS = 'EPS';

    public const METHODS = [
        self::AFTERPAY,
        self::CREDIT_CARD,
        self::IDEAL,
        self::BANCONTACT,
        self::PAY_SAFE_CARD,
        self::PAY_PAL,
        self::SOFORT,
        self::GIROPAY,
        self::EPS
    ];
}
