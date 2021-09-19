<?php


namespace Russellxrs\ZipImporter;

use Russellxrs\ZipImporter\Exceptions\FieldsNotMatchException;

class Transformer
{
    protected array $rawData;

    protected array $assocData;

    protected array $fields;

    protected array $funcArray;

    public function __construct(array $rawData, array $fields, array $funcArray = [])
    {
        $this->rawData = $rawData;

        $this->fields = $fields;

        $this->funcArray = $funcArray;
    }

    public static function load(array $array, array $fields, array $funcArray = []) : self
    {
        if(count($array[0]) !== count($fields)){
            throw new FieldsNotMatchException();
        }

        $instance = new static($array, $fields, $funcArray);

        $instance->transform();

        return $instance;
    }

    public function transform() : void
    {
        $this->assocData = array_map(array($this, 'map'), $this->rawData);
    }

    public function getRawData() : array
    {
        return $this->rawData;
    }

    public function getAssocData() : array
    {
        return $this->assocData;
    }

//    public function getColumn(string $fieldName) : array{
//        return array_column($this->assocData, $fieldName);
//    }

    private function map(array $row) : array{
        $mappedRow = [];

        for($index = 0; $index < count($row); $index++){
            $key = $this->fields[$index];

            $value = $row[$index];

            $mappedRow[$key] = $value;

            if(array_key_exists($key, $this->funcArray)){
                $mappedRow[$key] = call_user_func($this->funcArray[$key], $value);
            }
        }

        return $mappedRow;
    }
}