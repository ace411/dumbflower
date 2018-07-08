<?php

namespace Chemem\DumbFlower\Watcher;

use Chemem\DumbFlower\State;
use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use \Symfony\Component\Finder\Finder;
use \Yosymfony\ResourceWatcher\{ResourceWatcher, ResourceCacheMemory, Crc32ContentHash};
use function \Chemem\DumbFlower\Colors\{applyColor, genColor};
use function \Chemem\Bingo\Functional\Algorithms\{concat, compose, identity, partialLeft, partialRight, constantFunction};

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

function asyncWatch(Reader $finderInit, string $effect, array $args = []) : Reader
{
    $loop = \React\EventLoop\Factory::create();

    return $finderInit
        ->withReader(
            function (IO $fileSys) use ($args, $loop, $effect) {
                return Reader::of(
                    function (string $dir) use ($args, $loop, $fileSys, $effect) {
                        $acc = [];
                        $loop->addPeriodicTimer(
                            1/2,
                            function () use ($acc, $args, $fileSys, $effect) {
                                $applyFilter = partialLeft(
                                    'array_map', 
                                    partialRight(watcherFilter, $args, $effect)
                                );
                                $watcher = $fileSys->map(function ($fileSys) { return $fileSys->findChanges(); })->exec();
                                echo $watcher->hasChanges() ?
                                    concat(
                                        PHP_EOL, 
                                        applyColor('New: ', genColor(1)), 
                                        implode(', ', $applyFilter($watcher->getNewFiles())),
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

const watcherFilter = 'Chemem\\DumbFlower\\Watcher\\watcherFilter';

function watcherFilter(string $file, string $action, array $params = []) : string
{
    $filter = compose(
        \Chemem\DumbFlower\Filters\createImg,
        partialRight(\Chemem\DumbFlower\Filters\applyFilter, $action),
        partialRight(\Chemem\DumbFlower\Filters\extractImg, $file)
    );

    return $filter($file)
        ->run($params)
        ->flatMap(function ($success) use ($file, $action) { return $success ? concat(' ', $action, $file) : concat(' ', $file); });
}