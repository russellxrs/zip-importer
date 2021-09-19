<?php


namespace Test;


use PHPUnit\Framework\TestCase;

use Russellxrs\ZipImporter\Zipper;

class UnzipTest extends TestCase
{
    /** @test */
    function it_can_unzip_a_zip_file(){
        $zipper = Zipper::load(__DIR__ . '/stubs/example.zip')->unzip();

        $this->assertFileExists($zipper->destDir() . '/example.xls');

        $zipper->delTmpFolder();
    }

    /** @test */
    function it_can_unzip_a_rar_file(){
        $zipper = Zipper::load(__DIR__  . '/stubs/example.rar')->unzip();

        $this->assertFileExists($zipper->destDir() . '/example.xls');

        $zipper->delTmpFolder();
    }

    /** @test */
    function it_can_find_excel_file(){
        $zipper = Zipper::load(__DIR__  . '/stubs/example.rar')->unzip();

        try{
            $this->assertEquals($zipper->importFilePath(), $zipper->destDir() . '/example.xls');

            $zipper->delTmpFolder();
        }catch (\Exception $e){
            $zipper->delTmpFolder();
        }

    }
}