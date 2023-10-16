<?php

namespace App\Entity\Enum;

enum TransactionType: string {
    case OUTGOING = 'outgoing';
    case INCOMING = 'incoming';
}