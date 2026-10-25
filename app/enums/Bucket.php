<?php

namespace App\Enums;

enum Bucket: string
{
    case Living = 'living';
    case Debt = 'debt';
    case Savings = 'savings';
    case Other = 'other';
}
