<?php

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Reached = 'reached';
    case Abandoned = 'abandoned';
}
