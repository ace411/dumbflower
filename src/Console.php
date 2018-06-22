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
    extend,
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
                    ->map(partialRight('json_encode', JSON_PRETTY_PRINT));
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
        extractOutputFile
    );

    return $processArgs($args);
}

const extractCommand = 'Chemem\\DumbFlower\\Console\\extractCommand';

function extractCommand(array $args)
{
    $cmd = compose(
        \Chemem\Bingo\Functional\Algorithms\head,
        function ($cmd) { return !empty($cmd) && in_array($cmd, array_keys(State\CONSOLE_COMMANDS)) ? $cmd : identity(''); },
        function ($cmd) use ($args) { return !empty($cmd) ? extend(['cmd' => $cmd], ['def' => $args]) : logError(1); }
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
        function ($params) use ($args, $type, $error) { return $params == [""] ? logError($error) : extend($args, [$type => head($params)]); }
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

const logError = 'Chemem\\DumbFlower\\Console\\logError';

function logError(int $type)
{
    return patternMatch(
        [
            '"1"' => function () { return ['error' => 'Invalid command']; },
            '"2"' => function () { return ['error' => 'Invalid src file']; },
            '"3"' => function () { return ['error' => 'Invalid output file']; },
            '_' => function () { return ['error' => 'Unidentified error']; }
        ],
        $type
    );
}