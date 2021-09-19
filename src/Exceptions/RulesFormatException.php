<?php


namespace Russellxrs\ZipImporter\Exceptions;


use Throwable;

class RulesFormatException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->message = "Rules format is wrong";
        parent::__construct($message, $code, $previous);
    }
}