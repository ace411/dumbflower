<?php

namespace Chemem\DumbFlower\Async;

use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use \Symfony\Component\Finder\Finder;
use \Yosymfony\ResourceWatcher\{ResourceWatcher, ResourceCachePhpFile, Crc32ContentHash};
use function \Chemem\Bingo\Functional\Algorithms\{constantFunction};

const finderInit = 'Chemem\\DumbFlower\\Watcher\\finderInit';

function finderInit() : Reader
{
    return Reader::of(
        function (string $dir) {
            return (new Finder)
                ->files()
                ->in($dir);
        }
    );
}

const watcherInit = 'Chemem\\DumbFlower\\Watcher\\watcherInit';

function watcherInit(Reader $finderInit) : Reader
{
    return $finderInit
        ->withReader(
            function ($finder) {
                return Reader::of(
                    function (string $dir) use ($finder) {
                        $resource = (new ResourceWatcher(new ResourceCachePhpFile('cache.php'), $finder, new Crc32ContentHash))
                            ->initialize();

                        return IO::of(constantFunction($resource));
                    }
                );
            }
        );
}