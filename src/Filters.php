<?php

namespace Chemem\DumbFlower\Filters;

use \Chemem\Bingo\Functional\Functors\Monads\{IO, Reader, State};
use function Chemem\DumbFlower\Utilities\{isImg, renameImg, getImgExt};
use function \Chemem\Bingo\Functional\Algorithms\{
    head, 
    reverse, 
    compose, 
    extend,
    concat, 
    identity, 
    partialLeft, 
    partialRight,
    arrayKeysExist, 
    constantFunction
};
use function \Chemem\Bingo\Functional\PatternMatching\patternMatch;

const createImg = 'Chemem\\DumbFlower\\Filters\\createImg';

function createImg(string $image) : IO
{
    return IO::of(constantFunction($image))
        ->map(
            function (string $image) {
                return State::of($image)
                    ->map(function (string $image) { return isImg($image) ? getImgExt($image) : identity(''); });
            }
        )
        ->map(
            function (State $imgObj) {
                list($file, $ext) = $imgObj->exec();

                $resource = patternMatch(
                    [
                        '"gif"' => function () use ($file) { return @imagecreatefromgif($file); },
                        '"png"' => function () use ($file) { return @imagecreatefrompng($file); },
                        '"jpg"' => function () use ($file) { return @imagecreatefromjpeg($file); },
                        '"jpeg"' => function () use ($file) { return @imagecreatefromjpeg($file); },
                        '"webp"' => function () use ($file) { return @imagecreatefromwebp($file); },
                        '_' => function () { return false; }
                    ],
                    $ext
                );

                return [
                    'ext' => $ext,
                    'file' => $file, 
                    'resource' => $resource
                ];
            }
        );
}

const applyFilter = 'Chemem\\DumbFlower\\Filters\\applyFilter';

function applyFilter(IO $image, string $type) : Reader
{
    return Reader::of(
        function (array $opts) use ($image, $type) {
            return $image
                ->map(
                    function (array $imgOpts) use ($type, $opts) {
                        $filter = partialRight('imagefilter', ...reverse($opts));

                        $resource = is_resource($imgOpts['resource']) ?
                            patternMatch(
                                [
                                    '"smoothen"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_SMOOTH, $imgOpts['resource']); 
                                    },
                                    '"negate"' => function () use ($filter, $imgOpts) { 
                                        return $filter(IMG_FILTER_NEGATE, $imgOpts['resource']); 
                                    },
                                    '"grayscale"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_GRAYSCALE, $imgOpts['resource']);
                                    },
                                    '"colorize"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_COLORIZE, $imgOpts['resource']);
                                    },
                                    '"brightness"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_BRIGHTNESS, $imgOpts['resource']);
                                    },
                                    '"contrast"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_CONTRAST, $imgOpts['resource']);
                                    },
                                    '"emboss"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_EMBOSS, $imgOpts['resource']);
                                    },
                                    '"gaussian"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_GAUSSIAN_BLUR, $imgOpts['resource']);
                                    },
                                    '"blur"' => function () use ($filter, $imgOpts) {
                                        return $filter(IMG_FILTER_SELECTIVE_BLUR, $imgOpts['resource']);
                                    },
                                    '_' => function () { return false; }
                                ],
                                $type
                            ) :
                            identity(false);
                        
                        return extend($imgOpts, ['filtered' => $resource]);
                    }
                );
        }
    );
}

const extractImg = 'Chemem\\DumbFlower\\Filters\\extractImg';

function extractImg(Reader $resource, string $entity) : Reader
{
    return $resource
        ->withReader(
            function (IO $resourceData) use ($entity) {
                return Reader::of(
                    function (array $opts) use ($entity,$resourceData) {
                        return $resourceData
                            ->map(
                                function (array $imgOpts) use ($entity) {
                                    $extract = arrayKeysExist($imgOpts, 'ext', 'filtered') && $imgOpts['filtered'] ?
                                        concat('', 'image', $imgOpts['ext'])(
                                            $imgOpts['resource'],
                                            renameImg($imgOpts['file'], $entity)
                                        ) :
                                        identity(false);

                                    return $extract ? imagedestroy($imgOpts['resource']) : $extract;
                                }
                            );
                    }
                );
            }
        );
}