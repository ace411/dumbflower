<?php

namespace Chemem\DumbFlower\Console;

use Chemem\DumbFlower\State;
use \Chemem\Bingo\Functional\{
    Functors\Maybe\Maybe,
    Functors\Monads\IO,
    Functors\Monads\Reader
};
use function Chemem\Bingo\Functional\PatternMatching\{patternMatch};
use function Chemem\Bingo\Functional\Algorithms\{
    head, 
    pluck,
    every,
    extend,
    concat,
    compose, 
    identity, 
    partialLeft,
    partialRight, 
    constantFunction
};

const console = 'Chemem\\DumbFlower\\Console\\console';

function console(array $args) : IO
{
    return Maybe::fromValue($args)
        ->filter(function (array $args) { return !empty($args); })
        ->orElse(Maybe::fromValue($args))
        ->map(\Chemem\Bingo\Functional\Algorithms\tail)
        ->map(processArgs)
        ->flatMap(
            function (array $args) {
                return IO::of(constantFunction($args))
                    ->map(
                        function ($output) { 
                            $withKey = compose(
                                'array_keys',
                                \Chemem\Bingo\Functional\Algorithms\head,
                                partialLeft(\Chemem\Bingo\Functional\Algorithms\concat, ':'),
                                partialLeft(\Chemem\Bingo\Functional\Algorithms\concat, ' ')
                            );

                            return every(array_keys($output), function ($output) { return !is_int($output); }) ?
                                concat(PHP_EOL, $withKey($output), implode(PHP_EOL, $output)) :
                                implode(PHP_EOL, $output);
                        }
                    );
            }
        );
}

const runConsole = 'Chemem\\DumbFlower\\Console\\runConsole';

function runConsole() : Reader
{
    return Reader::of(
        function (array $args) {
            return console($args)
                ->flatMap(
                    partialLeft('printf', '%s')
                );
        }
    );
}

const processArgs = 'Chemem\\DumbFlower\\Console\\processArgs';

function processArgs(array $args)
{
    $processArgs = compose(
        extractCommand,
        extractFnArgs,
        extractSrcFile,
        extractOutputFile,
        execFunc
    );

    return $processArgs($args);
}

const extractCommand = 'Chemem\\DumbFlower\\Console\\extractCommand';

function extractCommand(array $args)
{
    $cmd = compose(
        \Chemem\Bingo\Functional\Algorithms\head,
        function ($cmd) { return !empty($cmd) && in_array($cmd, array_keys(State\CONSOLE_COMMANDS)) ? identity($cmd) : identity(''); },
        function ($cmd) use ($args) { 
            return !empty($cmd) ? 
                extend(['cmd' => $cmd], ['def' => $args]) : 
                extend(['def' => $args], logError(1)); 
        }
    );

    return $cmd($args);
}

const extractFnArgs = 'Chemem\\DumbFlower\\Console\\extractFnArgs';

function extractFnArgs(array $args) : array
{
    $replace = compose(
        getCmdArgs,
        partialLeft(\Chemem\Bingo\Functional\Algorithms\filter, partialLeft('preg_match', '/([{1})([0-9a-z\,\ ]*)(]{1})/')),
        function ($args) { return !empty($args) ? head($args) : identity(''); },
        partialLeft('str_replace', ' ', ''),
        partialLeft('str_replace', '[', ''),
        partialLeft('str_replace', ']', ''),
        partialLeft('explode', ','),
        function ($params) use ($args) { return $params == [""] ? extend($args, ['args' => []]) : extend($args, ['args' => $params]); }
    );
            
    return $replace($args);
}

const getCmdArgs = 'Chemem\\DumbFlower\\Console\\getCmdArgs';

function getCmdArgs(array $args)
{
    $extract = compose(
        partialRight(\Chemem\Bingo\Functional\Algorithms\pluck, 'def'),
        function ($args) { return !empty($args) ? identity($args) : identity([]); }
    );

    return $extract($args);
}

const extractFileDef = 'Chemem\\DumbFlower\\Console\\extractFileDef';

function extractFileDef(array $args, string $regex, string $type, int $error) : array
{
    $extractSrc = compose(
        getCmdArgs,
        partialLeft(\Chemem\Bingo\Functional\Algorithms\filter, partialLeft('preg_match', $regex)),
        function ($args) { return !empty($args) ? head($args) : identity(''); },
        partialLeft('explode', '='),
        \Chemem\Bingo\Functional\Algorithms\reverse,
        function ($params) use ($args, $type, $error) { 
            return $params == [""] ? 
                extend($args, logError($error)) : 
                extend($args, [$type => head($params)]); 
        }
    );

    return $extractSrc($args);
}

const extractSrcFile = 'Chemem\\DumbFlower\\Console\\extractSrcFile';

function extractSrcFile(array $args) 
{
    return extractFileDef(
        $args, 
        '/(--)(s{1})(=*)([a-z\.\-\_\0-9\ ]*)/', 
        'src', 
        2
    );
}

const extractOutputFile = 'Chemem\\DumbFlower\\Console\\extractOutputFile';

function extractOutputFile(array $args)
{
    return extractFileDef(
        $args, 
        '/(--)(o{1})(=*)([a-z\.\-\_\0-9\ ]*)/', 
        'out', 
        3
    );
}

const action = 'Chemem\\DumbFlower\\Console\\action';

function action(array $args, array $funcs)
{
    $filter = compose(
        partialRight(\Chemem\Bingo\Functional\Algorithms\arrayKeysExist, 'src', 'out', 'args'),
        function ($res) use ($args) { return $res ? identity($args) : logError(4); },
        function ($args) use ($funcs) {
            $apply = compose(...$funcs);

            return isset($args['func_error']) ?
                identity($args) :
                $apply($args['src'])
                    ->run($args['args'])
                    ->flatMap(function ($res) { return extend([State\CONSOLE_RESULT_MSG], [$res ? 'Success' : 'Failure']); });
        }
    );

    return $filter($args);
}

const execFunc = 'Chemem\\DumbFlower\\Console\\execFunc';

function execFunc(array $args)
{
    $filter = function (string $type) use ($args) {
        return identity([
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, $type),
            partialRight(\Chemem\DumbFlower\Filters\extractImg, pluck($args, 'out'))
        ]);
    };

    $resize = identity([
        \Chemem\DumbFlower\Resize\computeAspectRatio,
        \Chemem\DumbFlower\Resize\resizeImg,
        partialRight(\Chemem\DumbFlower\Filters\extractImg, pluck($args, 'out'))
    ]);

    return patternMatch(
        [
            '"resize"' => function () use ($args, $resize) { return action($args, $resize); },
            '"blur"' => function () use ($args, $filter) { return action($args, $filter('blur')); },
            '"emboss"' => function () use ($args, $filter) { return action($args, $filter('emboss')); },
            '"negate"' => function () use ($args, $filter) { return action($args, $filter('negate')); },
            '"contrast"' => function () use ($args, $filter) { return action($args, $filter('contrast')); },
            '"smoothen"' => function () use ($args, $filter) { return action($args, $filter('smoothen')); },
            '"gaussian"' => function () use ($args, $filter) { return action($args, $filter('gaussian')); },
            '"colorize"' => function () use ($args, $filter) { return action($args, $filter('colorize')); },
            '"grayscale"' => function () use ($args, $filter) { return action($args, $filter('grayscale')); },
            '"brightness"' => function () use ($args, $filter) { return action($args, $filter('brightness')); },
            '"help"' => function () { 
                return [
                    concat(
                        PHP_EOL, 
                        State\CONSOLE_INTRO_HELP,
                        State\CONSOLE_INTRO_CMD_DESC,
                        State\CONSOLE_INTRO_ARG_DESC,
                        State\CONSOLE_INTRO_SRC_DESC
                    )
                ]; 
            },
            '_' => function () use ($args) { 
                $errorHandler = compose(
                    'array_keys',
                    partialLeft(
                        \Chemem\Bingo\Functional\Algorithms\filter, 
                        partialLeft('preg_match', '/([a-z]*)(_)(error)/')
                    ),
                    'array_values',
                    function ($err) use ($args) {
                        $count = count($err);
                        $app = function (int $init = 0, array $acc = []) use ($err, $args, &$app, $count) {
                            if ($init >= $count) { return extend([State\CONSOLE_ERROR_MSG], $acc); }

                            $acc[] = $args[$err[$init]];

                            return $app($init + 1, $acc);
                        };

                        return $app();
                    }
                );
                return $errorHandler($args);
            }
        ],
        pluck($args, 'cmd')
    );
}

const logError = 'Chemem\\DumbFlower\\Console\\logError';

function logError(int $type)
{
    return patternMatch(
        [
            '"1"' => function () { return ['cmd_error' => 'Invalid command']; },
            '"2"' => function () { return ['src_error' => 'Invalid src file']; },
            '"3"' => function () { return ['out_error' => 'Invalid output file']; },
            '"4"' => function () { return ['func_error' => 'Missing function arguments']; },
            '_' => function () { return ['gen_error' => 'Unidentified error']; }
        ],
        $type
    );
}
