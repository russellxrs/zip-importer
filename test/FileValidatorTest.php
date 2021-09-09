<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Russellxrs\ZipImporter\FileValidator;

class FileValidatorTest extends TestCase
{
    protected string $dest;

    protected string $type;

    protected string $sizeLimit;

    protected array $fileNames;

    protected function setUp(): void
    {
        $this->dest = __DIR__ . '/stubs/files/';

        $this->sizeLimit = '20kb';

        $this->type = 'image';
    }

    /** @test */
    function it_returns_true_if_files_based_on_fileNames_exists(){
        $this->fileNames = ['101', '102', '103', '104'];

        $fileValidator = new FileValidator($this->dest, $this->type, $this->sizeLimit, $this->fileNames);

        $this->assertSame(true, $fileValidator->validate());
    }

    /** @test */
    function it_returns_false_if_files_based_on_fileNames_not_exists(){
        $this->fileNames = ['wrong_file'];

        $fileValidator = new FileValidator($this->dest, $this->type, $this->sizeLimit, $this->fileNames);

        $this->assertSame(false, $fileValidator->validate());

        $this->assertEquals([0], $fileValidator->invalidIndexes());
    }
    
    /** @test */
    function it_returns_false_if_file_size_is_over_limit(){
        $this->fileNames = ['301_1025bytes'];

        $this->type = 'txt';

        $this->sizeLimit = '1024';

        $fileValidator = new FileValidator($this->dest, $this->type, $this->sizeLimit, $this->fileNames);

        $this->assertSame(false, $fileValidator->validate());

        $this->assertEquals([0], $fileValidator->invalidIndexes());

        var_dump($fileValidator->errors());
    }

    /** @test */
    function it_returns_false_if_file_type_is_not_match(){
        $this->fileNames = ['101'];

        $this->type = 'txt';

        $fileValidator = new FileValidator($this->dest, $this->type, $this->sizeLimit, $this->fileNames);

        $this->assertSame(false, $fileValidator->validate());

        $this->assertEquals([0], $fileValidator->invalidIndexes());
    }
}
