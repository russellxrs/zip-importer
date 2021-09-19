<?php

namespace Russellxrs\ZipImporter;

use PhpOffice\PhpSpreadsheet\Worksheet\PageMargins;

class Zipper
{
    public string $sourceFile;

    public string $sourceDir;

    public string $destDir;

    public array $importFileExt = ['xls', 'xlsx'];

    public function __construct(string $sourceFile, array $importFileExt = [])
    {
        $this->sourceFile = $sourceFile;

        $this->sourceDir = dirname($this->sourceFile);

        if($importFileExt) $this->importFileExt = $importFileExt;
    }

    public static function load($source, $importFileExt = []) : self
    {
        return new self($source, $importFileExt);
    }

    public function unzip() : self
    {
        $fileExtension = pathinfo($this->sourceFile, PATHINFO_EXTENSION);

        switch ($fileExtension){
            case 'zip': $this->zip();break;
            case 'rar': $this->rar();break;
            default: throw new \Exception();
        }

        return $this;
    }

    private function zip(){
        $zipArchive = new \ZipArchive();

        $zipArchive->open($this->sourceFile);

        $zipArchive->extractTo($this->destDir());

        $zipArchive->close();
    }

    private function rar(){
        $archive = \RarArchive::open($this->sourceFile);

        $entries = $archive->getEntries();

        foreach ($entries as $entry) {
            $entry->extract($this->destDir());
        }

        $archive->close();
    }

    public function destDir() : string{
        if(isset($this->destDir)) return $this->destDir;

        return $this->destDir = $this->sourceDir . DIRECTORY_SEPARATOR . time();
    }

    public function delTmpFolder($path = ''){
        $dirPath = $path ?: $this->destDir;

        if(!$dirPath){
            return;
        }

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->delTmpFolder($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function delCompressedFile(){
        unlink($this->sourceFile);
    }

    public function importFilePath(){
        $matches = glob($this->destDir() . '/' . '*.{'. join(',', $this->importFileExt) .'}', GLOB_BRACE);

        if(!$matches){
            throw new \Exception('没有找到导入文件');
        }

        if(count($matches) > 1){
            throw new \Exception('找到多个导入文件，请检查');
        }

        return $matches[0];
    }
}