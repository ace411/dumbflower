<?php

namespace Chemem\DumbFlower\Tests;

use function Chemem\DumbFlower\Utilities\resolvePath;
use function Chemem\DumbFlower\Watcher\{finderInit, watcherInit};

class WatcherTest extends \PHPUnit\Framework\TestCase
{
    public function testFinderInitReturnsReaderInstance()
    {
        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            finderInit()
        );
    }

    public function testFinderInitReturnsSymfonyFinderInstanceWhenDirectoryIsProvided()
    {
        $this->assertInstanceOf(
            \Symfony\Component\Finder\Finder::class,
            finderInit()->run(resolvePath(1, 'src'))
        );
    }

    public function testWatcherInitReturnsReaderInstance()
    {
        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            watcherInit(finderInit())
        );
    }

    public function testWatcherInitReturnsIOInstanceWrappedInsideReaderInstance()
    {
        $watcher = watcherInit(finderInit())
            ->run(resolvePath(1, 'src'));

        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            $watcher
        );
    }

    public function testWatcherInitReturnsYoSymfonyWatcherInstanceWrappedInsideIOInstanceEncapsulatedInReaderInstance()
    {
        $watcher = watcherInit(finderInit())
            ->run(resolvePath(1, 'src'))
            ->exec();

        $this->assertInstanceOf(
            \Yosymfony\ResourceWatcher\ResourceWatcher::class,
            $watcher
        );
    }
}