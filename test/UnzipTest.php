<?php


namespace Test;


use PHPUnit\Framework\TestCase;

use Russellxrs\ZipImporter\Zipper;

class UnzipTest extends TestCase
{
    /** @test */
    function it_can_unzip_a_zip_file(){
        Zipper::load(__DIR__ . '/stubs/example.zip')->unzip();

        $fileName = __DIR__ . '/stubs/example.xls';

        $this->assertFileExists($fileName);

        unlink($fileName);
    }
}