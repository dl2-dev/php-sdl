<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use DL2\SDL\Json;
use Generator;
use Throwable;
use ValueError;

/**
 * @internal
 * @covers \DL2\SDL\Json
 */
final class JsonTest extends TestCase
{
    public function dataProviderCtor(): Generator
    {
        yield ['{"foo": "baz"}'];
        yield [['foo' => 'baz']];
        yield [10.25];
        yield [10];
        yield [null];
        yield [true];
        yield ['"foo"'];
    }

    /**
     * @dataProvider dataProviderCtor
     */
    public function testCtor(mixed $input): void
    {
        $json = new Json($input);

        foreach ($json as $j) {
            static::assertNotEmpty($j);
        }
    }

    public function testGetSet(): void
    {
        $json      = new Json('{}');
        $json->foo = 'baz';
        static::assertSame('baz', $json->foo);

        $str  = '"cannot modify a non-object json"';
        $json = new Json($str);
        static::assertSame("{$json}", $str);

        $this->expectException(ValueError::class);
        $json->foo = 'baz';
    }

    public function testWrite(): void
    {
        $path1 = 'tmp/json1.json';
        $path2 = 'tmp/json2.json';
        $json1 = Json::read('composer.json');
        $json2 = new Json($json1);

        $json1->name = 'json1';
        $json2->name = 'json2';

        $json1->write($path1);
        $json2->write($path2, false);

        static::assertJsonFileNotEqualsJsonFile($path1, $path2);
        static::assertJsonStringEqualsJsonFile($path1, (string) $json1);
        static::assertJsonStringEqualsJsonFile($path2, (string) $json2);

        // reload
        $json1 = Json::read($path1);
        $json2 = Json::read($path2);

        static::assertSame('json1', $json1->name);
        static::assertSame('json2', $json2->name);

        unlink($path1);
        unlink($path2);
    }

    // public function __construct(private mixed $json = null)
    // {
    //     switch (\gettype($json)) {
    //         case 'boolean':
    //         case 'integer':
    //         case 'double':
    //         case 'NULL':
    //             $json = $this->encode($json);

    //         // no break
    //         case 'string':
    //             /** @var mixed */
    //             $json = $this->decode("{$json}");

    //         // no break
    //         default:
    //             if (\is_array($json) || \is_object($json)) {
    //                 $this->json = new ArrayObject($json, ArrayObject::ARRAY_AS_PROPS);
    //             } else {
    //                 $this->json = $json;
    //             }
    //     }
    // }

    // public function __get(string $name): mixed
    // public function __set(string $name, mixed $value): void
    // public function __toString(): string
    // public function decode(string|Stringable $json, int $flags = 0): mixed
    // public function encode(mixed $value = null, int $flags = 0): string
    // public function getIterator(): Traversable
    // public static function read(string $filename): self
    // public function write(string $filename, bool $pretty = true): int

    // public static function setUpBeforeClass(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // public static function tearDownAfterClass(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // protected function setUp(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // protected function tearDown(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // protected function assertPostConditions(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // protected function assertPreConditions(): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    // }

    // protected function onNotSuccessfulTest(Throwable $t): void
    // {
    //     fwrite(STDOUT, __METHOD__ . "\n");
    //
    //     throw $t;
    // }
}
// 3528799
