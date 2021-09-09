<?php


namespace Russellxrs\ZipImporter;


use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    protected string $path;

    protected int $startRow;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public static function load($path): self
    {
        return new self($path);
    }

    public function read(): array
    {
        return IOFactory::load($this->path)->getActiveSheet()->toArray();
    }
}