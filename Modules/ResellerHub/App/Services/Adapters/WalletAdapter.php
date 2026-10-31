<?php

namespace Modules\ResellerHub\App\Services\Adapters;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Services\WalletService;
use Nwidart\Modules\Facades\Module;
use Throwable;

class WalletAdapter implements WalletAdapterInterface
{
    public function isAvailable(): bool
    {
        return Module::has('Wallet') && Module::isEnabled('Wallet') && class_exists(Wallet::class);
    }

    public function getBalance(User $user): int
    {
        if (!$this->isAvailable()) {
            return 0;
        }

        $wallet = Wallet::where('holder_type', get_class($user))
            ->where('holder_id', $user->id)
            ->first();

        return $wallet ? (int)$wallet->balance : 0;
    }

    public function withdraw(User $user, int $amount, mixed $source, string $description, array $meta = []): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            if (class_exists(WalletService::class)) {
                $transType = class_exists(TransactionType::class) ? TransactionType::PAYMENT : 'payment';
                app(WalletService::class)->withdraw(
                    $user,
                    $amount,
                    $transType,
                    $source,
                    $description,
                    $meta
                );
                return true;
            }

            $wallet = Wallet::where('holder_type', get_class($user))
                ->where('holder_id', $user->id)
                ->first();

            if ($wallet && (int)$wallet->balance >= $amount) {
                $wallet->decrement('balance', $amount);
                return true;
            }
        } catch (Throwable $e) {
            Log::error('[WalletAdapter] Withdraw failed: ' . $e->getMessage());
        }

        return false;
    }

    public function refund(User $user, int $amount, mixed $source, string $description, array $meta = []): bool
    {
        if (!$this->isAvailable() || $amount <= 0) {
            return false;
        }

        try {
            if (class_exists(WalletService::class)) {
                $transType = class_exists(TransactionType::class) ? TransactionType::REFUND : 'refund';
                app(WalletService::class)->deposit(
                    $user,
                    $amount,
                    $transType,
                    $source,
                    $description,
                    $meta
                );
                return true;
            }

            $wallet = Wallet::where('holder_type', get_class($user))
                ->where('holder_id', $user->id)
                ->first();

            if ($wallet) {
                $wallet->increment('balance', $amount);
                return true;
            }
        } catch (Throwable $e) {
            Log::error('[WalletAdapter] Refund failed: ' . $e->getMessage());
        }

        return false;
    }
}
