<?php

namespace App\Enums;

enum Compounding: string
{
    case None = 'none';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
