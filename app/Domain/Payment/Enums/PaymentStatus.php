<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
