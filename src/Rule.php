<?php


namespace Russellxrs\ZipImporter;

use Russellxrs\ZipImporter\Exceptions\RulesFormatException;

class Rule
{
    protected array $rules;

    protected array $fields;

    protected array $laravelRules = [];

    protected array $fileRules = [];

    protected array $headers = [];

    public function __construct($rules)
    {
        $this->rules = $rules;

        $this->parse();
    }

    public static function load($rules) : self
    {
        return new static($rules);
    }

    public function parse()
    {
        foreach($this->rules as $key => $rule) {
            [$field, $header] = $this->explodeKey($key);

            [$laravelRule, $fileRule] = $this->separateRules($rule, $field);

            $this->laravelRules[$field] = $laravelRule;

            if($fileRule) $this->fileRules[$field] = $fileRule;

            $this->headers[] = $header;
        }

        $this->fields = array_keys($this->laravelRules);
    }

    public function getLaravelRules() : array
    {
        return $this->laravelRules;
    }

    public function getFileRules() : array
    {
        return $this->fileRules;
    }

    public function getFields() : array
    {
        return $this->fields;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    private function explodeKey(string $key) : array
    {
        if(stripos($key, '|') === false){
            throw new RulesFormatException();
        }

        $exploded = explode('|', $key);

        $field = $exploded[0];
        $header = $exploded[1];

        if($field === "" || $header === ""){
            throw new RulesFormatException();
        }

        return [$field, $header];
    }

    private function separateRules(string $rule, string $field): array{
        if(stripos($rule, 'checkFile') === false){
            return [$rule, []];
        }

        preg_match("/checkfile<(.+):(.+)\|(.+)>/i", $rule, $matches);

        $fileRule = $matches[0];

        $rule = str_ireplace($fileRule, "", $rule);

        $rule = str_ireplace("||", "|", $rule);

        $rule = rtrim($rule, "|");

        $rule = ltrim($rule, "|");

        return [$rule, $fileRule];
    }
}