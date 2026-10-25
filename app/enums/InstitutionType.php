<?php

namespace App\Enums;

enum InstitutionType: string
{
    case Bank = 'bank';
    case Broker = 'broker';
    case SavingsProvider = 'savings_provider';
    case Crypto = 'crypto';
    case Other = 'other';
}
