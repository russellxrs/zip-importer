<?php

namespace Russellxrs\ZipImporter;

class Zipper
{
    public string $sourceFile;

    public string $sourceDir;

    public function __construct(string $sourceFile)
    {
        $this->sourceFile = $sourceFile;

        $this->sourceDir = dirname($this->sourceFile);
    }

    public static function load($source) : self
    {
        return new self($source);
    }

    public function unzip() : void
    {
        $zipArchive = new \ZipArchive();

        $zipArchive->open($this->sourceFile);

        $zipArchive->extractTo($this->sourceDir);

        $zipArchive->close();
    }
}