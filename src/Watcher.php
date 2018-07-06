<?php

namespace Chemem\DumbFlower\Watcher;

use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use \Symfony\Component\Finder\Finder;
use \Yosymfony\ResourceWatcher\{ResourceWatcher, ResourceCacheMemory, Crc32ContentHash};
use function \Chemem\DumbFlower\Colors\{applyColor, genColor};
use function \Chemem\Bingo\Functional\Algorithms\{concat, identity, constantFunction};

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
                        $resource = new ResourceWatcher(new ResourceCacheMemory(), $finder, new Crc32ContentHash);
                        $resource->initialize();

                        return IO::of(constantFunction($resource));
                    }
                );
            }
        );
}

const asyncWatch = 'Chemem\\DumbFlower\\Watcher\\asyncWatch';

function asyncWatch(Reader $finderInit) : Reader
{
    $loop = \React\EventLoop\Factory::create();

    return $finderInit
        ->withReader(
            function (IO $fileSys) use ($loop) {
                return Reader::of(
                    function (string $dir) use ($fileSys, $loop) {
                        $acc = [];
                        $loop->addPeriodicTimer(
                            1/2,
                            function () use ($fileSys, $acc) {
                                $watcher = $fileSys->map(function ($fileSys) { return $fileSys->findChanges(); })->exec();
                                echo $watcher->hasChanges() ?
                                    concat(
                                        PHP_EOL, 
                                        applyColor('New: ', genColor(1)), 
                                        implode(', ', $watcher->getNewFiles()),
                                        applyColor('Updated: ', genColor(2)), 
                                        implode(', ', $watcher->getUpdatedFiles()),
                                        applyColor('Deleted: ', genColor(4)), 
                                        implode(', ', $watcher->getDeletedFiles())
                                    ) :
                                    identity(null);

                                $acc[] = $fileSys->flatMap(function ($fileSys) { return $fileSys->findChanges()->hasChanges(); }); 
                            } 
                        );
                        $loop->run();
                        return $acc;
                    }
                );
            }
        );
}