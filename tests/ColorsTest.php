<?php

namespace Chemem\DumbFlower\Tests;

use function Chemem\DumbFlower\Colors\{genColor, applyColor};

class ColorsTest extends \PHPUnit\Framework\TestCase
{
    public function testGenColorOutputsString()
    {
        $this->assertInternalType('string', genColor(1));
    }

    /**
     * @dataProvider colorProvider
     */
    public function testGenColorOutputsColorSpecificToCode($colorCode, $expectedColor)
    {
        $this->assertEquals($expectedColor, genColor($colorCode));
    }

    public function colorProvider()
    {
        return [
            [1, 'green'],
            [2, 'blue'],
            [3, 'red'],
            [4, 'magenta'],
            [99, 'default']
        ];
    }

    public function testApplyColorOutputsString()
    {
        $this->assertInternalType('string', applyColor('foo', genColor(1)));
    }
}