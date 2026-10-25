<?php

namespace App\Enums;

enum MatchField: string
{
    case Description = 'description';
    case CounterpartyName = 'counterparty_name';
    case CounterpartyIban = 'counterparty_iban';
}
