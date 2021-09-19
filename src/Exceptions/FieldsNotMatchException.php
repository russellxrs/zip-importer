<?php

namespace Russellxrs\ZipImporter\Exceptions;

use Throwable;

class FieldsNotMatchException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->message = "Fields count and array count not match";
        parent::__construct($message, $code, $previous);
    }
}