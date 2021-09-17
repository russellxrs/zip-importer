<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Russellxrs\ZipImporter\FileValidator;
use ZipStream\File;

class FileValidatorTest extends TestCase
{
    protected string $dest;

    protected array $data;

    protected array $rules;

    protected function setUp(): void
    {
        $this->dest = __DIR__ . '/stubs';

        $this->data = ['field_a' => '101'];

        $this->rules = ['field_a' => 'checkFile<files:image|20kb>'];
    }


    /** @test */
    function it_passes_if_file_based_on_data_exists(){
        $fileValidator = FileValidator::make($this->data, $this->rules, $this->dest);

        $this->assertSame(true, $fileValidator->passes());
    }

    /** @test */
    function it_fails_if_file_based_on_data_not_exists(){
        $this->data = ['field_a' => 'wrong_file'];

        $fileValidator = FileValidator::make($this->data, $this->rules, $this->dest);

        $this->assertSame(true, $fileValidator->fails());
    }

    /** @test */
    function it_fails_if_file_size_is_over_limit(){
        $this->data = ['field_a' => '301_1025bytes'];

        $this->rules = ['field_a' => 'checkFile<files:txt|1kb>'];

        $fileValidator = FileValidator::make($this->data, $this->rules, $this->dest);

        $this->assertSame(true, $fileValidator->fails());
    }

    /** @test */
    function it_fails_if_file_type_is_not_match(){
        $this->data = ['field_a' => '101'];

        $this->rules = ['field_a' => 'checkFile<files:txt|20kb>'];

        $fileValidator = FileValidator::make($this->data, $this->rules, $this->dest);

        $this->assertSame(true, $fileValidator->fails());
    }

    /** @test */
    function it_returns_all_validated_files_as_an_array(){
        $fileValidator = FileValidator::make($this->data, $this->rules, $this->dest);

        $fileValidator->validate();

        $this->assertEquals(['field_a' => [$this->dest  . '/files/101.png']], $fileValidator->validated());
    }
}
