<?php

namespace ServerShellBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use ServerShellBundle\Enum\CommandStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CommandStatus::class)]
final class CommandStatusTest extends AbstractEnumTestCase
{
    #[TestWith([CommandStatus::PENDING, 'pending', '待执行'])]
    #[TestWith([CommandStatus::RUNNING, 'running', '执行中'])]
    #[TestWith([CommandStatus::COMPLETED, 'completed', '已完成'])]
    #[TestWith([CommandStatus::FAILED, 'failed', '失败'])]
    #[TestWith([CommandStatus::TIMEOUT, 'timeout', '超时'])]
    #[TestWith([CommandStatus::CANCELED, 'canceled', '已取消'])]
    public function testValueAndLabel(CommandStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $enum->value);
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    #[TestWith(['pending', CommandStatus::PENDING])]
    #[TestWith(['running', CommandStatus::RUNNING])]
    #[TestWith(['completed', CommandStatus::COMPLETED])]
    #[TestWith(['failed', CommandStatus::FAILED])]
    #[TestWith(['timeout', CommandStatus::TIMEOUT])]
    #[TestWith(['canceled', CommandStatus::CANCELED])]
    public function testFromValidInput(string $value, CommandStatus $expected): void
    {
        $this->assertEquals($expected, CommandStatus::from($value));
    }

    #[TestWith(['invalid_status'])]
    #[TestWith([''])]
    #[TestWith(['PENDING'])]
    #[TestWith(['unknown'])]
    #[TestWith(['123'])]
    public function testFromExceptionHandling(string $invalidValue): void
    {
        $this->expectException(\ValueError::class);
        CommandStatus::from($invalidValue);
    }

    #[TestWith(['pending', CommandStatus::PENDING])]
    #[TestWith(['running', CommandStatus::RUNNING])]
    #[TestWith(['completed', CommandStatus::COMPLETED])]
    #[TestWith(['failed', CommandStatus::FAILED])]
    #[TestWith(['timeout', CommandStatus::TIMEOUT])]
    #[TestWith(['canceled', CommandStatus::CANCELED])]
    public function testTryFromValidInput(string $value, CommandStatus $expected): void
    {
        $this->assertEquals($expected, CommandStatus::tryFrom($value));
    }

    #[TestWith(['invalid_status'])]
    #[TestWith([''])]
    #[TestWith(['PENDING'])]
    #[TestWith(['unknown'])]
    #[TestWith(['123'])]
    public function testTryFromInvalidInput(string $invalidValue): void
    {
        $this->assertNull(CommandStatus::tryFrom($invalidValue));
    }

    public function testValueUniqueness(): void
    {
        $cases = CommandStatus::cases();
        $values = array_map(fn ($case) => $case->value, $cases);

        $this->assertCount(count(array_unique($values)), $values, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $cases = CommandStatus::cases();
        $labels = array_map(fn ($case) => $case->getLabel(), $cases);

        $this->assertCount(count(array_unique($labels)), $labels, 'All enum labels must be unique');
    }

    /**
     * 测试枚举实例可以用于switch语句
     */
    public function testEnumInMatch(): void
    {
        // 测试每个枚举值的match表达式
        $testCases = [
            [CommandStatus::PENDING, 'pending'],
            [CommandStatus::RUNNING, 'running'],
            [CommandStatus::COMPLETED, 'completed'],
            [CommandStatus::FAILED, 'failed'],
            [CommandStatus::TIMEOUT, 'timeout'],
            [CommandStatus::CANCELED, 'canceled'],
        ];

        foreach ($testCases as [$status, $expected]) {
            $result = match ($status) {
                CommandStatus::PENDING => 'pending',
                CommandStatus::RUNNING => 'running',
                CommandStatus::COMPLETED => 'completed',
                CommandStatus::FAILED => 'failed',
                CommandStatus::TIMEOUT => 'timeout',
                CommandStatus::CANCELED => 'canceled',
            };

            $this->assertEquals($expected, $result);
        }
    }

    /**
     * 测试枚举标签功能
     */
    public function testGetLabel(): void
    {
        $this->assertEquals('待执行', CommandStatus::PENDING->getLabel());
        $this->assertEquals('执行中', CommandStatus::RUNNING->getLabel());
        $this->assertEquals('已完成', CommandStatus::COMPLETED->getLabel());
        $this->assertEquals('失败', CommandStatus::FAILED->getLabel());
        $this->assertEquals('超时', CommandStatus::TIMEOUT->getLabel());
        $this->assertEquals('已取消', CommandStatus::CANCELED->getLabel());
    }

    /**
     * 测试枚举选择功能
     */
    public function testSelectFunctionality(): void
    {
        $options = CommandStatus::genOptions();
        $this->assertNotEmpty($options);

        $item = CommandStatus::PENDING->toSelectItem();
        $this->assertArrayHasKey('label', $item);
        $this->assertArrayHasKey('value', $item);
        $this->assertEquals('待执行', $item['label']);
        $this->assertEquals('pending', $item['value']);
    }

    /**
     * 测试 toArray 方法
     */
    public function testToArray(): void
    {
        $array = CommandStatus::PENDING->toArray();
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertEquals('待执行', $array['label']);
        $this->assertEquals('pending', $array['value']);

        // 测试其他状态
        $array = CommandStatus::RUNNING->toArray();
        $this->assertEquals('执行中', $array['label']);
        $this->assertEquals('running', $array['value']);
    }
}
