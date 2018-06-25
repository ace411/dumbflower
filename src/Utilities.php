<?php

namespace Chemem\DumbFlower\Utilities;

use \Qaribou\Collection\ImmArray;
use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use function Chemem\Bingo\Functional\PatternMatching\{patternMatch};
use function \Chemem\Bingo\Functional\Algorithms\{
    head,
    tail,
    concat,
    extend, 
    compose,
    identity,
    foldRight, 
    partialLeft, 
    constantFunction
};

const resolvePath = 'Chemem\\DumbFlower\\Utilities\\resolvePath';

function resolvePath(int $level = 1, string ...$fragments) : string
{
    $path = concat('/', dirname(__DIR__, $level), ...$fragments);

    return $path;
}

const getImagesInDir = 'Chemem\\DumbFlower\\Utilities\\getImagesInDir';

function getImagesInDir(string $dir) : IO
{
    return IO::of(constantFunction($dir))
        ->map(
            function (string $dir) : ImmArray {
                $files = is_dir($dir) ? 
                    ImmArray::fromArray(scandir($dir)) : 
                    ImmArray::fromArray([]);

                return $files
                    ->map(function ($file) use ($dir) { return concat('/', $dir, $file); })
                    ->filter(isImg);
            }
        );
}

const manipDir = 'Chemem\\DumbFlower\\Utilities\\manipDir';

function manipDir(string $opt, string $dirname) : IO
{
    return IO::of(constantFunction($opt))
        ->map(
            function (string $opt) use ($dirname) : bool {
                $match = patternMatch(
                    [
                        '"create"' => function () use ($dirname) { return !file_exists($dirname) ? @mkdir($dirname) : identity(true); },
                        '"delete"' => function () use ($dirname) { return rmdir($dirname); },
                        '_' => function () { return false; }
                    ],
                    $opt
                );

                return !is_dir($dirname) ? identity(false) : $match;
            }
        );
}

const isImg = 'Chemem\\DumbFlower\\Utilities\\isImg';

function isImg(string $image) : bool
{
    $check = compose('getimagesize', 'is_array');

    return is_file($image) && $check($image);
}

const renameImg = 'Chemem\\DumbFlower\\Utilities\\renameImg';

function renameImg(string $oldName, string $newName) : string
{
    $rename = compose(
        partialLeft('explode', '/'),
        \Chemem\Bingo\Functional\Algorithms\reverse,
        function ($file) use ($newName) { return is_dir($newName) ? extend([head($file)], [$newName]) : extend([$newName], tail($file)); },
        \Chemem\Bingo\Functional\Algorithms\reverse,
        partialLeft('implode', '/')        
    );

    return $rename($oldName);
}

const getImgExt = 'Chemem\\DumbFlower\\Utilities\\getImgExt';

function getImgExt(string $filename) : string
{
    $extGet = compose(
        'mime_content_type', 
        partialLeft('explode', '/'), 
        \Chemem\Bingo\Functional\Algorithms\reverse,
        \Chemem\Bingo\Functional\Algorithms\head
    );

    return $extGet($filename);
}
