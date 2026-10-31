<?php

namespace Modules\ResellerHub\App\Services\Adapters;

use App\Models\User;

interface WalletAdapterInterface
{
    public function isAvailable(): bool;

    public function getBalance(User $user): int;

    public function withdraw(User $user, int $amount, mixed $source, string $description, array $meta = []): bool;

    public function refund(User $user, int $amount, mixed $source, string $description, array $meta = []): bool;
}
