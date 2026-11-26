<?php
namespace App\Services\Contracts;
use App\Models\Purchase;
interface PurchaseServiceInterface { public function createDraft(array $data,int $userId): Purchase; public function receive(int $purchaseId,int $userId): Purchase; }
