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

namespace Kafkiansky\PHPClick;

/**
 * @api
 */
final readonly class Column
{
    public static function string(string $value): ColumnValuer
    {
        return new Internal\StringColumn($value);
    }

    public static function bool(bool $value): ColumnValuer
    {
        return new Internal\BoolColumn($value);
    }

    public static function array(ColumnValuer ...$columns): ColumnValuer
    {
        return new Internal\ArrayColumn($columns);
    }

    /**
     * @param ColumnValuer[] ...$columns
     */
    public static function map(array ...$columns): ColumnValuer
    {
        return new Internal\MapColumn($columns);
    }

    public static function dateTime(\DateTimeInterface $value): ColumnValuer
    {
        return new Internal\DateTimeColumn($value);
    }

    public static function dateTime64(\DateTimeInterface $value): ColumnValuer
    {
        return new Internal\DateTime64Column($value);
    }

    public static function uint8(int $value): ColumnValuer
    {
        return new Internal\Uint8Column($value);
    }

    public static function int8(int $value): ColumnValuer
    {
        return new Internal\Int8Column($value);
    }

    public static function uint16(int $value): ColumnValuer
    {
        return new Internal\Uint16Column($value);
    }

    public static function int16(int $value): ColumnValuer
    {
        return new Internal\Int16Column($value);
    }

    public static function uint32(int $value): ColumnValuer
    {
        return new Internal\Uint32Column($value);
    }

    public static function int32(int $value): ColumnValuer
    {
        return new Internal\Int32Column($value);
    }

    public static function uint64(int $value): ColumnValuer
    {
        return new Internal\Uint64Column($value);
    }

    public static function int64(int $value): ColumnValuer
    {
        return new Internal\Int64Column($value);
    }

    public static function float32(float $value): ColumnValuer
    {
        return new Internal\Float32Column($value);
    }

    public static function float64(float $value): ColumnValuer
    {
        return new Internal\Float64Column($value);
    }

    public static function nullable(?ColumnValuer $value = null): ColumnValuer
    {
        return new Internal\NullableColumn($value);
    }
}
