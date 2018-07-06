<?php

namespace Chemem\DumbFlower\Colors;

use \Chemem\Bingo\Functional\Functors\Either;
use \JakubOnderka\PhpConsoleColor\ConsoleColor;
use function \Chemem\Bingo\Functional\PatternMatching\patternMatch;

const applyColor = 'Chemem\\DumbFlower\\Colors\\applyColor';

function applyColor(string $text, string $color) : string
{
    return Either\Either::right(new ConsoleColor)
        ->filter(function ($obj) { return $obj->isSupported(); }, $text)
        ->orElse(Either\Either::right($text))
        ->flatMap(function ($obj) use ($text, $color) { return $obj instanceof ConsoleColor ? $obj->apply($color, $text) : $obj; });
}

const genColor = 'Chemem\\DumbFlower\\Colors\\genColor';

function genColor(int $code) : string
{
    return patternMatch(
        [
            '"1"' => function () { return 'green'; },
            '"2"' => function () { return 'blue'; },
            '"3"' => function () { return 'red'; },
            '"4"' => function () { return 'magenta'; },
            '_' => function () { return 'default'; }
        ],
        $code
    );
}