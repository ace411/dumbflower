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
    extractDir,
    watchDir,
    extractAuxCmd
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

    public function logErrorOutputsIntegerDataProvider()
    {
        return [
            [1, ['cmd_error' => 'Invalid command']],
            [2, ['src_error' => 'Invalid src file']],
            [3, ['out_error' => 'Invalid output file']],
            [4, ['func_error' => 'Missing function arguments']],
            [99, ['gen_error' => 'Unidentified error']],
        ];
    }

    /**
     * @dataProvider logErrorOutputsIntegerDataProvider
     */
    public function testLogErrorOutputsIntegerCodeSpecificErrorMessage($errorInteger, $expectedArray)
    {
        $this->assertEquals($expectedArray, logError($errorInteger));
    }

    public function testLogErrorOutputsErrorAsArray()
    {
        $error = logError(99);

        $this->assertInternalType('array', $error);
        $this->assertArrayHasKey('gen_error', $error);
    }

    public function testExtractCommandOutputsArrayWithCommand()
    {
        $cmd = extractCommand(['resize']);

        $this->assertInternalType('array', $cmd);
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

        $this->assertInternalType('array', $args);
        $this->assertArrayHasKey('args', $args);
    }

    public function testGetCmdArgsOutputsArrayOfArgumentsFedToTheConsole()
    {
        $cmdArgs = getCmdArgs(['def' => ['resize', '[12, 13]']]);

        $this->assertInternalType('array', $cmdArgs);
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

        $this->assertInternalType('array', $newFile);
        $this->assertArrayHasKey('new', $newFile);
    }

    public function testExtractSrcFileMatchesSourceFileArgumentAndTheAccompanyingFile()
    {
        $srcFile = extractSrcFile(['def' => ['resize', '[12, 13]', '--s=file.png']]);

        $this->assertInternalType('array', $srcFile);
        $this->assertArrayHasKey('src', $srcFile);
    }

    public function testExtractOutputFileMatchesOutputFileArgumentAndTheAccompanyingFile()
    {
        $outputFile = extractOutputFile(['def' => ['resize', '[12, 13]', '--o=file-smooth.png']]);

        $this->assertInternalType('array', $outputFile);
        $this->assertArrayHasKey('out', $outputFile);
    }

    public function testExtractDirMatchesSourceDirectoryArgumentAndTheAccompanyingDirectory()
    {
        $extractDir = extractDir(['def' => ['watch', '--dir=src']]);

        $this->assertInternalType('array', $extractDir);
        $this->assertArrayHasKey('dir', $extractDir);
    }

    public function testExtractAuxCmdAddsAuxiliaryCommandToArgumentList()
    {
        $aux = extractAuxCmd(['def' => ['watch', '--acmd=grayscale']]);

        $this->assertInternalType('array', $aux);
        $this->assertArrayHasKey('aux', $aux);
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

    public function testWatchDirOutputsErrorUponDetectionOfMissingArguments()
    {
        $this->assertArrayHasKey("func_error", watchDir($this->testArgs));
    }
}
