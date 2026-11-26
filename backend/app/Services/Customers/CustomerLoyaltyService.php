<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\CustomerLoyaltyTransaction;
use App\Repositories\Contracts\CustomerLoyaltyRepositoryInterface;
use App\Services\Contracts\CustomerLoyaltyServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerLoyaltyService implements CustomerLoyaltyServiceInterface
{
    public function __construct(
        private readonly CustomerLoyaltyRepositoryInterface $transactions
    ) {}

    public function apply(int $customerId, array $data, int $userId): CustomerLoyaltyTransaction
    {
        return DB::transaction(function () use ($customerId, $data, $userId) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);
            $points = (int) $data['points'];
            $type = $data['type'];

            $change = match ($type) {
                'earn' => $points,
                'redeem' => -$points,
                'adjustment' => ($data['direction'] ?? 'add') === 'subtract' ? -$points : $points,
                default => 0,
            };

            $newBalance = (int) $customer->points_balance + $change;

            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'points' => 'The customer does not have enough loyalty points.',
                ]);
            }

            $customer->forceFill(['points_balance' => $newBalance])->save();

            return $this->transactions->create([
                'customer_id' => $customer->id,
                'user_id' => $userId,
                'type' => $type,
                'points_change' => $change,
                'balance_after' => $newBalance,
                'note' => $data['note'] ?? null,
            ]);
        });
    }
}
