<?php

namespace App\Enums;

enum SnapshotSource: string
{
    case Manual = 'manual';
    case Derived = 'derived';
    case Import = 'import';
}
