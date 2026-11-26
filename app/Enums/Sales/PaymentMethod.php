<?php
namespace App\Enums\Sales;
enum PaymentMethod: string { case Cash='cash'; case Card='card'; case MobileBanking='mobile_banking'; case Bank='bank'; }
