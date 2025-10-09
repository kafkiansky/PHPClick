<?php

/**
 * MIT License
 * Copyright (c) 2024 kafkiansky.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Kafkiansky\PHPClick\Internal;

use Kafkiansky\Binary\Buffer;
use Kafkiansky\PHPClick\ColumnValuer;

/**
 * @internal
 */
final readonly class DateTime64Column implements ColumnValuer
{
    /** @var list<int> */
    private const POW10 = [
        1, 10, 100, 1_000, 10_000, 100_000, 1_000_000, 10_000_000, 100_000_000, 1_000_000_000
    ];

    /**
     * @param \DateTimeInterface $value
     * @param int $precision DateTime64(N) where N is precision, 0–9 | 3 by default
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private \DateTimeInterface $value,
        private int $precision = 3,
    ) {
        if ($this->precision < 0 || $this->precision > 9) {
            throw new \InvalidArgumentException('DateTime64 precision must be 0–9.');
        }
    }

    public function write(Buffer $buffer): void
    {
        $seconds = $this->value->getTimestamp();
        $microseconds = (int) $this->value->format('u');

        $base = $seconds * self::POW10[$this->precision];

        if ($this->precision === 0) {
            $fraction = 0; // truncate fractional part entirely
        } elseif ($this->precision <= 6) {
            // downscale microseconds to 10^-N with integer truncation
            $fraction = intdiv($microseconds, self::POW10[6 - $this->precision]);
        } else {
            // upscale microseconds to 10^-N
            $fraction = $microseconds * self::POW10[$this->precision - 6];
        }

        $buffer->writeInt64($base + $fraction);
    }
}
