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
    logError,
    execFunc,
    extractDir
};
use function \Chemem\Bingo\Functional\Algorithms\{pluck, extend, partialRight, arrayKeysExist};

class ConsoleTest extends TestCase
{
    protected $testArgs = [
        'cmd' => 'smoothen',
        'args' => \Chemem\DumbFlower\State\DEFAULT_SMOOTH_FILTER,
        'src' => 'foo/bar.png',
        'out' => 'baz.png'
    ];

    public function testLogErrorOutputsIntegerCodeSpecificErrorMessage()
    {
        $this->assertEquals(logError(1), ['cmd_error' => 'Invalid command']);
        $this->assertEquals(logError(2), ['src_error' => 'Invalid src file']);
        $this->assertEquals(logError(3), ['out_error' => 'Invalid output file']);
        $this->assertEquals(logError(4), ['func_error' => 'Missing function arguments']);
        $this->assertEquals(logError(99), ['gen_error' => 'Unidentified error']);
    }

    public function testLogErrorOutputsErrorAsArray()
    {
        $error = logError(99);

        $this->assertTrue(is_array($error));
        $this->assertArrayHasKey('gen_error', $error);
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

        $this->assertInternalType('array', $cmd);
        $this->assertArrayHasKey('cmd_error', $cmd);
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

    public function testExtractDirMatchesSourceDirectoryArgumentAndTheAccompanyingDirectory()
    {
        $extractDir = extractDir(['def' => ['watch', '--dir=src']]);

        $this->assertInternalType('array', $extractDir);
        $this->assertArrayHasKey('dir', $extractDir);
    }
    
    public function testProcessArgsOutputsArrayWithOperationStatusContents()
    {
        $args = processArgs(['resize', '[12,13]', '--s=file.png', '--o=file-smooth.png']);

        $this->assertInternalType('array', $args);
        $this->assertContains(\Chemem\DumbFlower\State\CONSOLE_RESULT_MSG, $args);
        $this->assertContains('Failure', $args);
    }

    public function testActionFunctionOutputsArray()
    {
        $filter = [
            \Chemem\DumbFlower\Filters\createImg,
            partialRight(\Chemem\DumbFlower\Filters\applyFilter, 'smoothen'),
            partialRight(\Chemem\DumbFlower\Filters\extractImg, pluck($this->testArgs, 'out'))
        ];

        $this->assertInternalType('array', $filter);
    }

    public function testExecFuncPerformsActionBasedOnCommandType()
    {
        $args = execFunc($this->testArgs);

        $this->assertInternalType('array', $args);
        $this->assertContains(\Chemem\DumbFlower\State\CONSOLE_RESULT_MSG, $args);
        $this->assertContains('Failure', $args);
    }

    public function testExecFuncOutputsErrorMessageEncapsulatedInArrayForInvalidCommands()
    {
        $args = execFunc(extend($this->testArgs, ['cmd' => 'foo']));

        $this->assertContains(\Chemem\DumbFlower\State\CONSOLE_ERROR_MSG, $args);
    }
}