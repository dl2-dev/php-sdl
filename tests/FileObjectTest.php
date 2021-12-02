<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use DL2\SDL\FileObject;
use DL2\SDL\IOException;
use LogicException;

/**
 * @internal
 * @covers \DL2\SDL\FileObject
 */
final class FileObjectTest extends TestCase
{
    public function testCtor(): void
    {
        new FileObject('composer.json');

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/not a valid mode for fopen/i');
        new FileObject('composer.json', 'none');
    }

    public function testMkdir(): void
    {
        $pathname = FileObject::resolveFilename('tmp/tests', false);
        static::assertTrue(FileObject::mkdir($pathname));
        static::assertDirectoryExists($pathname);
    }

    public function testReadWrite(): void
    {
        $expectedFile = FileObject::resolveFilename('tmp/composer.json', false);
        $actualFile   = FileObject::resolveFilename('composer.json', false);
        $content      = FileObject::read('composer.json');

        static::assertIsInt(FileObject::write($expectedFile, $content));
        static::assertJsonFileEqualsJsonFile($expectedFile, $actualFile);

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('/failed to open stream/i');
        FileObject::read('file-not-found');
    }

    public function testResolveFilename(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/non-empty.+string/i');
        FileObject::resolveFilename('', true);
    }
}
