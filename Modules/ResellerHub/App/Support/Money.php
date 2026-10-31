<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Support;

use InvalidArgumentException;
use JsonSerializable;
use Modules\ResellerHub\App\Helpers\CurrencyConverter;


final class Money implements JsonSerializable
{
    public const DEFAULT_CURRENCY = 'toman';

    private function __construct(
        public readonly int    $amount,
        public readonly string $currency,
    )
    {
    }

    public static function of(int|float|string|null $amount, ?string $currency = null): self
    {
        $normalized = self::normalizeCurrency($currency);

        if ($amount === null || $amount === '') {
            return new self(0, $normalized);
        }

        if (!is_numeric($amount)) {
            throw new InvalidArgumentException(
                sprintf('Amount must be numeric, %s given.', get_debug_type($amount))
            );
        }

        return new self((int)round((float)$amount), $normalized);
    }

    public static function zero(?string $currency = null): self
    {
        return new self(0, self::normalizeCurrency($currency));
    }

    /**
     * Normalize a caller-supplied currency code.
     */
    public static function normalizeCurrency(?string $currency): string
    {
        $clean = strtolower(trim((string)$currency));

        return match ($clean) {
            '', 'toman', 'tomans', 'irt', 'تومان' => self::DEFAULT_CURRENCY,
            'rial', 'rials', 'irr', 'ریال' => 'rial',
            default => self::DEFAULT_CURRENCY,
        };
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Clamp at zero. Used where a negative amount is meaningless (a discount
     * larger than the subtotal must not produce a negative payable).
     */
    public function atLeastZero(): self
    {
        return $this->amount < 0 ? new self(0, $this->currency) : $this;
    }

    /**
     * Apply a percentage, rounding half up exactly once.
     * used for tax; keeping it here means the rounding rule has exactly one
     * definition instead of one per call site.
     */
    public function percentage(float $percent): self
    {
        return new self((int)round($this->amount * $percent / 100), $this->currency);
    }

    public function multiply(int|float $multiplier): self
    {
        return new self((int)round($this->amount * $multiplier), $this->currency);
    }

    /**
     * Split this amount into $parts integer shares using the largest-remainder
     * @return array<int, self>
     */
    public function allocate(int $parts): array
    {
        if ($parts < 1) {
            throw new InvalidArgumentException('Cannot allocate money into fewer than one part.');
        }

        $base = intdiv($this->amount, $parts);
        $remainder = $this->amount - ($base * $parts);

        $shares = [];
        for ($i = 0; $i < $parts; $i++) {
            $shares[$i] = $base + ($i < $remainder ? 1 : 0);
        }

        return array_map(fn(int $share): self => new self($share, $this->currency), $shares);
    }

    /**
     * @param array<int, int|float> $weights
     * @return array<int, self>
     */
    public function allocateByWeights(array $weights): array
    {
        $weights = array_map(static fn($w): float => max(0.0, (float)$w), array_values($weights));
        $totalWeight = array_sum($weights);

        if ($totalWeight <= 0.0) {
            return $this->allocate(max(1, count($weights)));
        }

        $raw = [];
        $assigned = 0;

        foreach ($weights as $index => $weight) {
            $exact = $this->amount * ($weight / $totalWeight);
            $floor = (int)floor($exact);
            $raw[$index] = ['floor' => $floor, 'remainder' => $exact - $floor];
            $assigned += $floor;
        }

        $leftover = $this->amount - $assigned;

        // Hand out the remaining units to the largest remainders first.
        uksort($raw, static function (int|string $a, int|string $b) use ($raw): int {
            return $raw[$b]['remainder'] <=> $raw[$a]['remainder'];
        });

        foreach (array_keys($raw) as $index) {
            if ($leftover <= 0) {
                break;
            }
            $raw[$index]['floor']++;
            $leftover--;
        }

        $shares = [];
        foreach ($raw as $index => $row) {
            $shares[$index] = new self((int)$row['floor'], $this->currency);
        }

        ksort($shares);

        return $shares;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function min(self $other): self
    {
        $this->assertSameCurrency($other);

        return $this->amount <= $other->amount ? $this : $other;
    }

    public function format(bool $persianDigits = true): string
    {
        return CurrencyConverter::formatPrice($this->amount, $this->currency, $persianDigits);
    }

    public function label(): string
    {
        return CurrencyConverter::currencyLabel($this->currency);
    }

    /**
     * @return array{amount: int, currency: string}
     */
    public function toArray(): array
    {
        return ['amount' => $this->amount, 'currency' => $this->currency];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(sprintf(
                'Cannot operate on %s and %s: amounts in different currencies have no defined result. '
                . 'Convert explicitly with CurrencyConverter first.',
                $this->currency,
                $other->currency,
            ));
        }
    }
}
