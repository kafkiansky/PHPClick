<?php

declare(strict_types=1);

namespace Kafkiansky\PHPClick\Tests;

use Kafkiansky\Binary\Buffer;
use Kafkiansky\Binary\Endianness;
use Kafkiansky\PHPClick\Column;
use Kafkiansky\PHPClick\ColumnValuer;
use Kafkiansky\PHPClick\Internal\ArrayColumn;
use Kafkiansky\PHPClick\Internal\BoolColumn;
use Kafkiansky\PHPClick\Internal\DateTime64Column;
use Kafkiansky\PHPClick\Internal\DateTimeColumn;
use Kafkiansky\PHPClick\Internal\Float32Column;
use Kafkiansky\PHPClick\Internal\Float64Column;
use Kafkiansky\PHPClick\Internal\Int16Column;
use Kafkiansky\PHPClick\Internal\Int32Column;
use Kafkiansky\PHPClick\Internal\Int64Column;
use Kafkiansky\PHPClick\Internal\Int8Column;
use Kafkiansky\PHPClick\Internal\MapColumn;
use Kafkiansky\PHPClick\Internal\NullableColumn;
use Kafkiansky\PHPClick\Internal\StringColumn;
use Kafkiansky\PHPClick\Internal\Uint16Column;
use Kafkiansky\PHPClick\Internal\Uint32Column;
use Kafkiansky\PHPClick\Internal\Uint64Column;
use Kafkiansky\PHPClick\Internal\Uint8Column;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Column::class)]
#[CoversClass(ArrayColumn::class)]
#[CoversClass(MapColumn::class)]
#[CoversClass(BoolColumn::class)]
#[CoversClass(DateTime64Column::class)]
#[CoversClass(DateTimeColumn::class)]
#[CoversClass(Float32Column::class)]
#[CoversClass(Float64Column::class)]
#[CoversClass(Int8Column::class)]
#[CoversClass(Uint8Column::class)]
#[CoversClass(Int16Column::class)]
#[CoversClass(Uint16Column::class)]
#[CoversClass(Int32Column::class)]
#[CoversClass(Uint32Column::class)]
#[CoversClass(Int64Column::class)]
#[CoversClass(Uint64Column::class)]
#[CoversClass(StringColumn::class)]
#[CoversClass(NullableColumn::class)]
final class ColumnTest extends TestCase
{
    /**
     * @return iterable<array-key, array{ColumnValuer, callable(Buffer): mixed, mixed}>
     */
    public static function fixtures(): iterable
    {
        yield 'int8' => [
            Column::int8(1),
            static fn (Buffer $buffer): int => $buffer->consumeInt8(),
            1,
        ];

        yield 'uint8' => [
            Column::uint8(1),
            static fn (Buffer $buffer): int => $buffer->consumeUint8(),
            1,
        ];

        yield 'int16' => [
            Column::int16(1),
            static fn (Buffer $buffer): int => $buffer->consumeInt16(),
            1,
        ];

        yield 'uint16' => [
            Column::uint16(1),
            static fn (Buffer $buffer): int => $buffer->consumeUint16(),
            1,
        ];

        yield 'int32' => [
            Column::int32(1),
            static fn (Buffer $buffer): int => $buffer->consumeInt32(),
            1,
        ];

        yield 'uint32' => [
            Column::uint32(1),
            static fn (Buffer $buffer): int => $buffer->consumeUint32(),
            1,
        ];

        yield 'int64' => [
            Column::int64(1),
            static fn (Buffer $buffer): int => $buffer->consumeInt64(),
            1,
        ];

        yield 'uint64' => [
            Column::uint64(1),
            static fn (Buffer $buffer): int => $buffer->consumeUint64(),
            1,
        ];

        yield 'true' => [
            Column::bool(true),
            static fn (Buffer $buffer): int => $buffer->consumeUint8(),
            1,
        ];

        yield 'false' => [
            Column::bool(false),
            static fn (Buffer $buffer): int => $buffer->consumeUint8(),
            0,
        ];

        yield 'float32' => [
            Column::float32(1.5),
            static fn (Buffer $buffer): float => $buffer->consumeFloat(),
            1.5,
        ];

        yield 'float64' => [
            Column::float64(1.5),
            static fn (Buffer $buffer): float => $buffer->consumeDouble(),
            1.5,
        ];

        yield 'string' => [
            Column::string('test'),
            static fn (Buffer $buffer): string => $buffer->consume(
                self::assertPositive($buffer->consumeVarUint()),
            ),
            'test',
        ];

        yield 'non-nullable(string)' => [
            Column::nullable(
                Column::string('test'),
            ),
            static function (Buffer $buffer): string {
                $buffer->consumeUint8(); // nullability

                return $buffer->consume(
                    self::assertPositive($buffer->consumeVarUint()),
                );
            },
            'test',
        ];

        yield 'nullable(string)' => [
            Column::nullable(),
            static function (Buffer $buffer): ?string {
                $buffer->consumeUint8(); // nullability

                return null;
            },
            null,
        ];

        yield 'datetime' => [
            Column::dateTime(
                (new \DateTimeImmutable())->setTimestamp(1719758949),
            ),
            static fn (Buffer $buffer): \DateTimeImmutable => (new \DateTimeImmutable())->setTimestamp(
                $buffer->consumeUint32(),
            ),
            (new \DateTimeImmutable())->setTimestamp(1719758949),
        ];

        yield 'datetime64' => [
            Column::dateTime64(
                (new \DateTimeImmutable())->setTimestamp(1719758949),
                6,
            ),
            static function (Buffer $buffer): \DateTimeImmutable {
                $timestamp = $buffer->consumeInt64();

                $seconds = $timestamp / 1000000;
                $microseconds = $timestamp % 1000000;

                return (new \DateTimeImmutable())
                    ->setTimestamp((int)$seconds)
                    ->modify("+ $microseconds microseconds");
            },
            (new \DateTimeImmutable())->setTimestamp(1719758949),
        ];

        yield 'string[]' => [
            Column::array(
                Column::string('test1'),
                Column::string('test2'),
            ),
            static function (Buffer $buffer): array {
                $n = $buffer->consumeVarUint();

                $values = \array_fill(0, $n, '');

                for ($i = 0; $i < $n; $i++) {
                    $values[$i] = $buffer->consume(
                        self::assertPositive($buffer->consumeVarUint()),
                    );
                }

                return $values;
            },
            ['test1', 'test2'],
        ];

        yield '[string]string' => [
            Column::map(
                [Column::string('key'), Column::string('value')],
            ),
            static function (Buffer $buffer): array {
                $n = $buffer->consumeVarUint();

                $values = [];

                for ($i = 0; $i < $n; $i++) {
                    $values[$buffer->consume(self::assertPositive($buffer->consumeVarUint()))] = $buffer->consume(
                        self::assertPositive($buffer->consumeVarUint()),
                    );
                }

                return $values;
            },
            ['key' => 'value'],
        ];
    }

    /**
     * @template T
     * @param callable(Buffer): T $read
     * @param T                   $value
     */
    #[DataProvider('fixtures')]
    public function testColumn(ColumnValuer $column, callable $read, mixed $value): void
    {
        $buffer = Buffer::empty(Endianness::little());

        $column->write($buffer);
        self::assertEquals($value, $read($buffer));
        self::assertCount(0, $buffer);
    }

    /**
     * @return int<1, max>
     */
    private static function assertPositive(int $n): int
    {
        \assert($n > 0, 'Number should be positive.');

        return $n;
    }
}
