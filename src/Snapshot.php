<?php

namespace Chemem\DumbFlower\Snapshot;

use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use function \Chemem\Bingo\Functional\Algorithms\{compose, identity, constantFunction};

function takeSnapshot() : Reader
{
    return Reader::of(
        function (string $format) {
            return IO::of(constantFunction($format))
                ->map(
                    function (string $format) {
                        $snap = compose(
                            constantFunction(imagegrabscreen()),
                            function ($res) use ($format) {
                                return is_resource($res) ? 
                                    identity([
                                        'ext' => $format,
                                        'resource' => $res,
                                        'filtered' => identity(true)
                                    ]) :
                                    identity([]);
                            }
                        );

                        return PHP_OS == 'WINNT' ? $snap($format) : identity([]);
                    }
                );
        }
    );
}