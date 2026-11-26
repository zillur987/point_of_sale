<?php
namespace App\Enums\Purchases;
enum PurchaseStatus: string
{
    case Draft = 'draft';
    case Received = 'received';
    case Cancelled = 'cancelled';
}
