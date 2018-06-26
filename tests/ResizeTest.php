<?php

namespace Chemem\DumbFlower\Tests;

use PHPUnit\Framework\TestCase;
use function \Chemem\Bingo\Functional\Algorithms\{compose, partialLeft};
use function \Chemem\DumbFlower\Resize\{computeAspectRatio, resizeImg};

class ResizeTest extends TestCase
{
    public function testComputeAspectRatioOutputsIOInstance()
    {
        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            computeAspectRatio('img/file.png')
        );
    }

    public function testComputeAspectRatioOutputsArrayWrappedInsideIOInstance()
    {
        $specs = computeAspectRatio('img/file.png')->exec();

        $this->assertTrue(is_array($specs));
        $this->assertEquals(
            $specs, 
            [
                'ratio' => 0,
                'width' => 0,
                'height' => 0,
                'file' => '',
                'ext' => ''
            ]
        );
    }

    public function resizeImgOutputsReaderInstance()
    {
        $resize = compose(
            \Chemem\DumbFlower\Resize\computeAspectRatio,
            \Chemem\DumbFlower\Resize\resizeImg
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            $resize('img/file.png')
        );
    }

    public function resizeImgOutputsIOInstanceWrappedInsideReaderMonad()
    {
        $resize = compose(
            \Chemem\DumbFlower\Resize\computeAspectRatio,
            \Chemem\DumbFlower\Resize\resizeImg
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $resize('img/file.png')->run([100, 100])
        );
    }

    public function resizeImgOutputsArrayWrappedInsideIOMonadEncapsulatedInReaderMonad()
    {
        $func = compose(
            \Chemem\DumbFlower\Resize\computeAspectRatio,
            \Chemem\DumbFlower\Resize\resizeImg
        );

        $resize = $func('img/file.png')->run([100, 100])->exec();

        $this->assertTrue(is_array($resize));
    }

    public function testResizeMultipleOutputsIOInstance()
    {
        $files = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            \Chemem\DumbFlower\Resize\resizeMultiple
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $files('src')
        );
    }

    public function testResizeMultipleOutputsImmutableListWrappedInsideIOInstance()
    {
        $files = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            \Chemem\DumbFlower\Resize\resizeMultiple
        );

        $this->assertInstanceOf(
            \Qaribou\Collection\ImmArray::class,
            $files('src')->exec()
        );
    }
}