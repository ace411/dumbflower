<?php

namespace Chemem\DumbFlower\Tests;

use PHPUnit\Framework\TestCase;
use function \Chemem\DumbFlower\Snapshot\{takeSnapshot};

class SnapshotTest extends TestCase
{
    public function testTakeSnapshotReturnsReaderInstance()
    {
        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\Reader::class,
            takeSnapshot()
        );
    }

    public function testTakeSnapshotWrapsIOInstanceInReaderMonad()
    {
        $this->assertInstanceOf(
            \Chemem\Bingo\Functional\Functors\Monads\IO::class,
            takeSnapshot()
                ->run('png')
        );
    }

    public function testTakeSnapshotWrapsArrayInsideReaderEncapsulatedIOInstance()
    {
        $snapshot = takeSnapshot()
            ->run('png')
            ->exec();

        $this->assertTrue(is_array($snapshot)); 
    }
}
