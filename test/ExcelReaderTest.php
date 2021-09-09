<?php


namespace Test;

use PHPUnit\Framework\TestCase;
use Russellxrs\ZipImporter\ExcelReader;

class ExcelReaderTest extends TestCase
{
    /** @test */
    function it_can_read_xls_file_as_an_array(){
        $content = ExcelReader::load(__DIR__ . '/stubs/reader-example.xls')->read();

        $expected = [
            ['1a', '1b', '1c', '1d', '1e'],
            ['2a', '2b', '2c', '2d', '2e'],
            ['3a', '3b', '3c', '3d' , '3e']
        ];

        $this->assertEquals($expected, $content);
    }
}