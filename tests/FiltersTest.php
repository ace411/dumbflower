<?php

namespace Chemem\DumbFlower\Tests;

use PHPUnit\Framework\TestCase;
use function \Chemem\Bingo\Functional\Algorithms\{compose, partialLeft, partialRight, arrayKeysExist};
use function \Chemem\DumbFlower\Filters\{createImg, applyFilter};

class FiltersTest extends TestCase
{
    public function testCreateImgOutputsIOInstance()
    {
        $createImg = createImg('foo/bar');

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $createImg
        );
    }

    public function testCreateImgOutputsArrayWrappedInIOInstance()
    {
        $createImg = createImg('foo/bar')->exec();

        $this->assertTrue(is_array($createImg));
        $this->assertTrue(arrayKeysExist($createImg, 'ext', 'file', 'resource'));
    }

    public function testApplyFilterOutputsReaderInstance()
    {
        $filter = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            $filter('foo/bar.png')
        );
    }

    public function testApplyFilterOutputsIOInstanceWrappedInReaderInstance()
    {
        $filter = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $filter('foo/bar.png')->run([])
        );
    }

    public function testApplyFilterOutputsArrayWrappedInsideIOInstanceEncapsulatedInReaderInstance()
    {
        $filter = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen')
        );

        $this->assertArrayHasKey(
            'filtered', 
            $filter('foo/bar.png')
                ->run([])
                ->exec()
        );
    }

    public function testExtractImgOutputsReaderInstance()
    {
        $extract = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen'),
            partialRight(\Chemem\DumbFlower\FIlters\extractImg, 'file-smooth.png')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            $extract('foo/bar.png')
        );
    }

    public function testExtractImgOutputsIOInstanceWrappedInsideReaderInstance()
    {
        $extract = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen'),
            partialRight(\Chemem\DumbFlower\FIlters\extractImg, 'file-smooth.png')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $extract('foo/bar.png')->run([])
        );
    }

    public function testExtractImgOutputsBooleanValueWrappedInsideIOInstanceEncapsulatedInReaderInstance()
    {
        $extract = compose(
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen'),
            partialRight(\Chemem\DumbFlower\FIlters\extractImg, 'file-smooth.png')
        );

        $this->assertTrue(
            is_bool(
                $extract('foo/bar.png')
                    ->run([])
                    ->exec()
            )
        );
    }

    public function testFilterMultipleOutputsIOInstance()
    {
        $filter = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            partialRight(\Chemem\DumbFlower\Filters\filterMultiple, 'smoothen')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $filter('src')
        );
    }

    public function testFilterMultipleOutputsImmutableListWrappedInsideIOInstance()
    {
        $filter = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            partialRight(\Chemem\DumbFlower\Filters\filterMultiple, 'smoothen')
        );
        
        $this->assertInstanceOf(
            \Qaribou\Collection\ImmArray::class,
            $filter('src')->exec()
        );
    }

    public function testExtractMultipleOutputsReaderInstance()
    {
        $extr = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            partialRight(\Chemem\DumbFlower\Filters\filterMultiple, 'smoothen'),
            partialRight(\Chemem\DumbFlower\Filters\extractMultiple, 'dir')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            $extr('src')
        );
    }

    public function testExtractMultipleOutputsIOInstanceWrappedInsideReaderInstance()
    {
        $extr = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            partialRight(\Chemem\DumbFlower\Filters\filterMultiple, 'smoothen'),
            partialRight(\Chemem\DumbFlower\Filters\extractMultiple, dirname(__DIR__) . '/dir')
        );

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $extr('src')->run([100])
        );
    }

    public function testExtractMultipleOutputsImmutableListWrappedInsideIOInstanceEncapsulatedInReaderInstance()
    {
        $extr = compose(
            partialLeft(\Chemem\DumbFlower\Utilities\resolvePath, 1),
            \Chemem\DumbFlower\Utilities\getImagesInDir,
            partialRight(\Chemem\DumbFlower\Filters\filterMultiple, 'smoothen'),
            partialRight(\Chemem\DumbFlower\Filters\extractMultiple, dirname(__DIR__) . '/dir')
        );

        $this->assertInstanceOf(
            \Qaribou\Collection\ImmArray::class,
            $extr('src')
                ->run([100])
                ->exec()
        );
    }
}
