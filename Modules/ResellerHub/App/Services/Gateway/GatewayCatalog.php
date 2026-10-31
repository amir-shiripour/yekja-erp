<?php

namespace Modules\ResellerHub\App\Services\Gateway;

use Modules\Settings\Entities\Setting;
use Throwable;

final class GatewayCatalog
{
    /**
     * Return list of active payment gateways configured in system settings with valid credentials.
     *
     * Gateways must be marked 'active' AND have their required credentials configured in Settings
     * so they are actually connectable at checkout time.
     *
     * @return array<string, array{id: string, name: string, title: string, description: string, icon: string, is_default: bool}>
     */
    public static function getAvailableGateways(): array
    {
        try {
            $settings = Setting::whereIn('key', [
                'active_payment_methods',
                'default_payment_gateway',
                'zarinpal_status',
                'zarinpal_merchant_id',
                'zibal_status',
                'zibal_merchant_id',
                'behpardakht_status',
                'behpardakht_terminal_id',
                'behpardakht_username',
                'behpardakht_password',
                'sep_status',
                'sep_terminal_id',
            ])->pluck('value', 'key');
        } catch (Throwable) {
            $settings = collect();
        }

        // Check if global payment methods allow online payments
        $rawActiveMethods = $settings['active_payment_methods'] ?? null;
        if ($rawActiveMethods !== null) {
            $methods = is_string($rawActiveMethods) ? json_decode($rawActiveMethods, true) : (array)$rawActiveMethods;
            if (is_array($methods) && !in_array('online', $methods, true)) {
                return [];
            }
        }

        $default = $settings['default_payment_gateway'] ?? null;
        if ($default === 'saman') {
            $default = 'sep';
        }

        $definitions = [
            'zarinpal' => [
                'id' => 'zarinpal',
                'name' => 'زرین‌پال',
                'title' => 'درگاه پرداخت زرین‌پال',
                'description' => 'پرداخت امن با کلیه کارت‌های عضو شتاب',
                'status_key' => 'zarinpal_status',
                'icon' => 'zarinpal',
                'is_configured' => !empty(trim((string)($settings['zarinpal_merchant_id'] ?? ''))),
            ],
            'zibal' => [
                'id' => 'zibal',
                'name' => 'زیبال',
                'title' => 'درگاه پرداخت زیبال',
                'description' => 'پرداخت مطمئن با کلیه کارت‌های عضو شتاب',
                'status_key' => 'zibal_status',
                'icon' => 'zibal',
                'is_configured' => !empty(trim((string)($settings['zibal_merchant_id'] ?? ''))),
            ],
            'behpardakht' => [
                'id' => 'behpardakht',
                'name' => 'به‌پرداخت ملت',
                'title' => 'درگاه به‌پرداخت بانک ملت',
                'description' => 'درگاه پرداخت شاپرک بانک ملت',
                'status_key' => 'behpardakht_status',
                'icon' => 'mellat',
                'is_configured' => !empty(trim((string)($settings['behpardakht_terminal_id'] ?? '')))
                    && !empty(trim((string)($settings['behpardakht_username'] ?? '')))
                    && !empty(trim((string)($settings['behpardakht_password'] ?? ''))),
            ],
            'sep' => [
                'id' => 'sep',
                'name' => 'سامان کیش (سپ)',
                'title' => 'درگاه سامان کیش (سپ)',
                'description' => 'درگاه پرداخت پرداخت الکترونیک سامان',
                'status_key' => 'sep_status',
                'icon' => 'saman',
                'is_configured' => !empty(trim((string)($settings['sep_terminal_id'] ?? ''))),
            ],
        ];

        $gateways = [];
        foreach ($definitions as $id => $info) {
            $status = $settings[$info['status_key']] ?? 'inactive';
            // Gateway must be both active in Settings AND have required credentials configured to connect
            if ($status === 'active' && !empty($info['is_configured'])) {
                $gateways[$id] = [
                    'id' => $info['id'],
                    'name' => $info['name'],
                    'title' => $info['title'],
                    'description' => $info['description'],
                    'icon' => $info['icon'],
                    'status_key' => $info['status_key'],
                    'is_default' => ($default === $id),
                ];
            }
        }

        return $gateways;
    }

    /**
     * Get default active online payment gateway key.
     */
    public static function getDefaultGateway(): string
    {
        $available = self::getAvailableGateways();

        try {
            $default = Setting::where('key', 'default_payment_gateway')->value('value');
            if ($default === 'saman') {
                $default = 'sep';
            }
        } catch (Throwable) {
            $default = null;
        }

        if ($default && isset($available[$default])) {
            return $default;
        }

        if (!empty($available)) {
            return (string)array_key_first($available);
        }

        return '';
    }

    /**
     * Check if a specific gateway is active.
     */
    public static function isGatewayActive(string $gateway): bool
    {
        $normalized = ($gateway === 'saman') ? 'sep' : $gateway;
        $available = self::getAvailableGateways();
        return isset($available[$normalized]);
    }

    /**
     * Get Persian label for gateway.
     */
    public static function getGatewayName(string $gateway): string
    {
        $normalized = ($gateway === 'saman') ? 'sep' : $gateway;
        $available = self::getAvailableGateways();
        if (isset($available[$normalized])) {
            return $available[$normalized]['name'];
        }

        return match ($normalized) {
            'zarinpal' => 'زرین‌پال',
            'zibal' => 'زیبال',
            'behpardakht' => 'به‌پرداخت ملت',
            'sep', 'saman' => 'سامان کیش (سپ)',
            default => $gateway ?: 'درگاه بانکی',
        };
    }
}
