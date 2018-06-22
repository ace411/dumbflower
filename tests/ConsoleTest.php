<?php

namespace Chemem\DumbFlower\Tests;

use PHPUnit\Framework\TestCase;
use function \Chemem\DumbFlower\Console\{
    console, 
    runConsole, 
    extractCommand, 
    processArgs, 
    extractFnArgs, 
    getCmdArgs,
    extractFileDef,
    extractSrcFile,
    extractOutputFile,
    logError
};
use function \Chemem\Bingo\Functional\Algorithms\arrayKeysExist;

class ConsoleTest extends TestCase
{
    public function testLogErrorOutputsIntegerCodeSpecificErrorMessage()
    {
        $this->assertEquals(logError(1), ['error' => 'Invalid command']);
    }

    public function testLogErrorOutputsErrorAsArray()
    {
        $error = logError(99);

        $this->assertTrue(is_array($error));
        $this->assertArrayHasKey('error', $error);
    }

    public function testExtractCommandOutputsArrayWithCommand()
    {
        $cmd = extractCommand(['resize']);

        $this->assertTrue(is_array($cmd));
        $this->assertArrayHasKey('cmd', $cmd);
    }

    public function testExtractCommandOutputsArrayWithErrorMethodWhenCommandIsNotFound()
    {
        $cmd = extractCommand(['foo']);

        $this->assertTrue(is_array($cmd));
        $this->assertArrayHasKey('error', $cmd);
    }

    public function testExtractFnArgsOutputsArrayWithArgs()
    {
        $args = extractFnArgs(['resize', '[12, 13]']);

        $this->assertTrue(is_array($args));
        $this->assertArrayHasKey('args', $args);
    }

    public function testGetCmdArgsOutputsArrayOfArgumentsFedToTheConsole()
    {
        $cmdArgs = getCmdArgs(['def' => ['resize', '[12, 13]']]);

        $this->assertTrue(is_array($cmdArgs));
        $this->assertEquals($cmdArgs, ['resize', '[12, 13]']);
    }

    public function testExtractFileDefOutputsFileFlagAndItsAccompanyingArgument()
    {
        $newFile = extractFileDef(
            ['def' => ['resize', '[12, 13]', '--n=file.png']], 
            '/(--)(n{1})(=*)([a-z\.\-\_\0-9\ ]*)/',
            'new',
            99
        );

        $this->assertTrue(is_array($newFile));
        $this->assertArrayHasKey('new', $newFile);
    }

    public function testExtractSrcFileMatchesSourceFileArgumentAndTheAccompanyingFile()
    {
        $srcFile = extractSrcFile(['def' => ['resize', '[12, 13]', '--s=file.png']]);

        $this->assertTrue(is_array($srcFile));
        $this->assertArrayHasKey('src', $srcFile);
    }

    public function testExtractOutputFileMatchesOutputFileArgumentAndTheAccompanyingFile()
    {
        $outputFile = extractOutputFile(['def' => ['resize', '[12, 13]', '--o=file-smooth.png']]);

        $this->assertTrue(is_array($outputFile));
        $this->assertArrayHasKey('out', $outputFile);
    }
    
    public function testProcessArgsOutputsArrayOfUsefulArguments()
    {
        $args = processArgs(['resize', '[12, 13]', '--s=file.png', '--o=file-smooth.png']);

        $this->assertTrue(is_array($args));
        $this->assertTrue(arrayKeysExist($args, 'def', 'cmd', 'args', 'src', 'out'));
    }
}