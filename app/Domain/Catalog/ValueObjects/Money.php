<?php

declare(strict_types=1);

namespace App\Domain\Catalog\ValueObjects;

/**
 * Value Object for handling monetary values safely.
 * Prevents floating-point precision issues in financial calculations.
 */
readonly class Money
{
    private const int SCALE = 2;
    private const int CENTS_PER_DOLLAR = 100;

    /**
     * Create a new Money instance.
     *
     * @param int $amount Amount in cents (smallest currency unit)
     * @param string $currency ISO 4217 currency code (default: USD)
     */
    private function __construct(
        private int $amount,
        private string $currency = 'USD',
    ) {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    /**
     * Create Money from decimal amount (e.g., 19.99).
     *
     * @param float $amount
     * @param string $currency
     * @return self
     */
    public static function fromDecimal(float $amount, string $currency = 'USD'): self
    {
        $cents = (int) round($amount * self::CENTS_PER_DOLLAR, 0, PHP_ROUND_HALF_UP);

        return new self($cents, $currency);
    }

    /**
     * Create Money from cents (e.g., 1999 cents = $19.99).
     *
     * @param int $cents
     * @param string $currency
     * @return self
     */
    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, $currency);
    }

    /**
     * Get amount as decimal.
     *
     * @return float
     */
    public function toDecimal(): float
    {
        return $this->amount / self::CENTS_PER_DOLLAR;
    }

    /**
     * Get amount in cents.
     *
     * @return int
     */
    public function toCents(): int
    {
        return $this->amount;
    }

    /**
     * Get the currency code.
     *
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add another Money value to this one.
     *
     * @param Money $other
     * @return self
     * @throws \InvalidArgumentException
     */
    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another Money value from this one.
     *
     * @param Money $other
     * @return self
     * @throws \InvalidArgumentException
     */
    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw new \InvalidArgumentException('Subtraction would result in negative amount.');
        }

        return new self($result, $this->currency);
    }

    /**
     * Multiply Money by a factor.
     *
     * @param float $multiplier
     * @return self
     */
    public function multiply(float $multiplier): self
    {
        $result = (int) round($this->amount * $multiplier, 0, PHP_ROUND_HALF_UP);

        return new self($result, $this->currency);
    }

    /**
     * Check if this Money equals another.
     *
     * @param Money $other
     * @return bool
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    /**
     * Check if this Money is greater than another.
     *
     * @param Money $other
     * @return bool
     */
    public function greaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount;
    }

    /**
     * Check if this Money is less than another.
     *
     * @param Money $other
     * @return bool
     */
    public function lessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount < $other->amount;
    }

    /**
     * Format Money for display (e.g., "$19.99").
     *
     * @return string
     */
    public function format(): string
    {
        $symbol = $this->getCurrencySymbol();
        $formatted = number_format($this->toDecimal(), 2, '.', ',');

        return $symbol . $formatted;
    }

    /**
     * Get currency symbol.
     *
     * @return string
     */
    private function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            default => $this->currency . ' ',
        };
    }

    /**
     * Ensure both Money instances have the same currency.
     *
     * @param Money $other
     * @throws \InvalidArgumentException
     */
    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot operate on Money with different currencies: %s and %s',
                    $this->currency,
                    $other->currency
                )
            );
        }
    }

    /**
     * Convert to string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
