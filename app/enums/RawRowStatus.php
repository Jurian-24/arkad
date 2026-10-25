<?php

namespace App\Enums;

enum RawRowStatus: string
{
    case Parsed = 'parsed';
    case Error = 'error';
}
