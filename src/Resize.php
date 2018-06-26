<?php

namespace Chemem\DumbFlower\Resize;

use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader};
use function Chemem\DumbFlower\Utilities\{isImg, getImgExt};
use function Chemem\Bingo\Functional\PatternMatching\patternMatch;
use function \Chemem\Bingo\Functional\Algorithms\{
    every, 
    extend,
    concat,
    compose, 
    identity, 
    partialLeft, 
    partialRight,
    constantFunction
};

const computeAspectRatio = 'Chemem\\DumbFlower\\Resize\\computeAspectRatio';

function computeAspectRatio(string $image) : IO
{
    return IO::of(constantFunction($image))
        ->map(function (string $image) { return isImg($image) ? getimagesize($image) : identity([0, 0]); })
        ->bind(
            function (array $dim) use ($image) { 
                list($width, $height) = $dim; 
                $cond = $width !== 0 && $height !== 0;
                
                return [
                    'ratio' => $cond ? $width / $height : identity(0),
                    'width' => $width,
                    'height' => $height,
                    'file' => $cond ? $image : identity(''),
                    'ext' => $cond ? getImgExt($image) : identity('') 
                ]; 
            }
        ); 
}

const resizeImg = 'Chemem\\DumbFlower\\Resize\\resizeImg';

function resizeImg(IO $aspectRatio) : Reader
{
    return Reader::of(
        function (array $opts) use ($aspectRatio) {
            return $aspectRatio
                ->map(
                    function (array $imgOpts) use ($opts) {
                        $resDimen = compose(
                            function (array $opts) {
                                return every($opts, function ($val) { return is_int($val) || is_numeric($val); }) &&
                                    count($opts) == 2 ?
                                        identity([
                                            'rwidth' => $opts[0], 
                                            'rheight' => $opts[1],
                                            'rratio' => $opts[0] / $opts[1]
                                        ]) :
                                        identity([
                                            'rwidth' => 0, 
                                            'rheight' => 0,
                                            'rratio' => 0
                                        ]);
                            },
                            partialLeft('array_merge', $imgOpts)
                        );

                        return $resDimen($opts); 
                    }
                )
                ->bind(
                    function (array $imgOpts) {
                        $sample = partialRight(
                            'imagecopyresampled', 
                            $imgOpts['height'], 
                            $imgOpts['width'], 
                            $imgOpts['rheight'], 
                            $imgOpts['rwidth'],
                            ...[0, 0, 0, 0]
                        );

                        $resize = compose(
                            function ($opts) { 
                                return $opts['rratio'] > $opts['ratio'] ? 
                                    extend($opts, ['rwidth' => $opts['rheight'] * $opts['ratio']]) :
                                    extend($opts, ['rheight' => $opts['rwidth'] / $opts['ratio']]); 
                            },
                            function ($opts) { 
                                return $opts['ratio'] !== 0 ? 
                                    extend(
                                        $opts, 
                                        ['resource' => imagecreatetruecolor($opts['rwidth'], $opts['rheight'])]
                                    ) :
                                    identity($opts);
                            },
                            function ($opts) { 
                                return isset($opts['resource']) ? 
                                    extend(
                                        $opts,
                                        [
                                            'orig' => patternMatch(
                                                [
                                                    '"jpg"' => function () { return imagecreatefromjpeg($opts['file']); },
                                                    '"jpeg"' => function () use ($opts) { return imagecreatefromjpeg($opts['file']); },
                                                    '_' => function () use ($opts) { 
                                                        return concat('', 'imagecreatefrom', $opts['ext'])($opts['file']); 
                                                    } 
                                                ],
                                                $opts['ext']
                                            )
                                        ]
                                    ) :
                                    identity($opts);
                            },
                            function ($opts) use ($sample) {
                                return isset($opts['orig']) ? 
                                    extend($opts, ['filtered' => $sample($opts['orig'], $opts['resource'])]) :
                                    extend($opts, ['filtered' => identity(false)]);
                            }
                        ); 

                        return $resize($imgOpts);
                    }
                );
        }
    );
}
