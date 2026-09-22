<?php

namespace App\Http\Traits;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\Log;

trait TransactionsTrait
{
    public function getAdminRoleId()
    {
        return User::role(RoleEnum::ADMIN)->first()?->id;
    }

    public function debitTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function creditTransaction($model, $amount, $detail, $order_id = null)
    {
        Log::info('creditTransaction');
        Log::info($model);
        Log::info($amount);
        Log::info($detail);
        Log::info($order_id);
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $order_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $order_id = null, $transaction_id = null)
    {
        Log::info('storeTransaction');
        Log::info($model);
        Log::info($type);
        Log::info($detail);
        Log::info($amount);
        Log::info($order_id);
        Log::info($transaction_id);
        $transaction = $model->histories()?->create([
            'amount' => $amount,
            'order_id' => $order_id,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getAdminRoleId(),
        ]);

        return $transaction;
    }
}
