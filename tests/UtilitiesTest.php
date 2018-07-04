<?php

namespace Chemem\DumbFlower\Tests;

use \PHPUnit\Framework\TestCase;
use function \Chemem\Bingo\Functional\Algorithms\{compose, partialLeft};
use function \Chemem\DumbFlower\Utilities\{
    isImg,
    manipDir,
    renameImg,
    resolvePath,
    getImgExt,
    getImagesInDir
};

class UtilitiesTest extends TestCase
{
    public function testResolvePathOutputsString()
    {
        $path = resolvePath(1, 'foo', 'bar');

        $this->assertInternalType('string', $path);
    }

    public function testResolvePathOutputsPathConcatenatedOntoSpecifiedBaseDirectory()
    {
        $path = resolvePath(1, 'foo', 'bar');

        $this->assertEquals($path, dirname(__DIR__) . '/foo/bar');
    }

    public function testGetImagesInDirOutputsIOInstance()
    {
        $contents = getImagesInDir(dirname(__DIR__));

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $contents
        );
    }

    public function testGetImagesInDirOutputsImmutableListOfFilesWrappedInsideIOMonad()
    {
        $contents = getImagesInDir(dirname(__DIR__))
            ->exec();

        $this->assertEquals($contents->toArray(), []);
    }

    public function testManipDirOutputsIOInstance()
    {
        $manip = manipDir('modify', 'foo');

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $manip
        );
    }

    public function testManipDirCreatesDirectoryWhenPromptedTo()
    {
        $manip = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            partialLeft(\Chemem\DumbFlower\Utilities\manipDir, 'create')
        );

        $this->assertTrue($manip('dir')->exec());
    }

    public function testManipDirDeletesDirectoryWhenPromptedTo()
    {
        $manip = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            partialLeft(\Chemem\DumbFlower\Utilities\manipDir, 'delete')
        );

        $this->assertFalse($manip('dir')->exec());
    }

    public function testIsImgDeterminesIfFileIsAnImage()
    {
        $isImg = isImg(dirname(__DIR__) . '/composer.json');

        $this->assertFalse($isImg);
    }

    public function testRenameImgRenamesImageFile()
    {
        $rename = renameImg('foo/bar.png', 'baz.png');

        $this->assertEquals($rename, 'foo/baz.png');
    }

    public function testRenameImgConcatenatesDirectoryAndFileNamesWhenDirectoryIsDetected()
    {
        $this->assertEquals(
            renameImg('bar.png', dirname(__DIR__) . '/vendor'),
            dirname(__DIR__) . '/vendor/bar.png'
        );
    }

    public function testGetImgExtOutputsFileExtension()
    {
        $ext = getImgExt(dirname(__DIR__) . '/composer.json');

        $this->assertEquals($ext, 'plain');
    }
}
