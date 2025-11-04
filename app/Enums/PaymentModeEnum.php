<?php

namespace App\Enums;

enum PaymentModeEnum: string
{
    case Cash = 'cash';
    case Card = 'card';
    case UPI = 'upi';
    case BankTransfer = 'bank-transfer';
}
