<?php


namespace Russellxrs\ZipImporter;


use Russellxrs\ZipImporter\Exceptions\RulesFormatException;

class FileValidator
{
    protected string $dest;

    protected array $data;

    protected array $rules;

    protected array $errors = [];

    public function __construct($data, $rules, $dest)
    {
        $this->data = $data;

        $this->rules = $rules;

        $this->dest = $dest;
    }

    public static function make($data, $rules, $dest): self
    {
        $instance = new static($data, $rules, $dest);

        $instance->validate();

        return $instance;
    }

    public function validate(): bool
    {
        array_map([$this, 'validateRow'], array_keys($this->rules));
    }

    public function validateRow  ($field): bool
    {
        $row = array_column($this->data, $field);

        $rule = $this->rules[$field];

        [$folder, $types, $sizeLimit] = self::parseRule($rule);

        foreach ($row as $index => $fileName) {
            $matches = glob($this->dest . '/' . $folder . '/' . $fileName . '*');

            if (!$matches) {
                $this->addError($field, '没有找到对应文件');
                continue;
            }

            if (count($matches) > 1) {
                $this->addError($field, '存在多个重名文件，请检查');
                continue;
            }

            $targetFile = $matches[0];

            if (count($matches) === 1) {
                $pathInfo = pathinfo($targetFile);

                $fileExtension = $pathInfo['extension'];

                if (!in_array($fileExtension, $types)) {
                    $this->addError($field, '文件格式不正确，必须是' . join('、', $types));
                    continue;
                }

                if (filesize($targetFile) > $sizeLimit) {
                    $this->addError($field, '文件超出' . self::getReadableSizeLimit($sizeLimit) . ',请压缩后上传');
                    continue;
                }
            }
        }

        return count($this->errors) === 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function passes(): bool
    {
        return count($this->errors) === 0;
    }

    public function failed(): bool
    {
        return !$this->passes();
    }


    public function addError($field, $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;

        $this->errors[$field] = array_unique($this->errors[$field]);
    }

    public static function parseRule($rule): array
    {
        preg_match("/checkfile<(.+):(.+)\|(.+)>/i", $rule, $matches);

        $folder = $matches[1];

        $types = self::parseType($matches[2]);

        $sizeLimit = self::parseFileSizeLimit($matches[3]);

        if (!$folder || !$types || !$sizeLimit) {
            throw new RulesFormatException();
        }

        return [$folder, $types, $sizeLimit];
    }

    public static function parseType($rawType): array
    {
        switch ($rawType) {
            case 'image':
                $types = ['jpg', 'jpeg', 'gif', 'png'];
                break;
            case 'office':
                $types = ['xls', 'xlsx', 'doc', 'docx'];
                break;
            case 'excel':
                $types = ['xls', 'xlsx'];
                break;
            case 'word':
                $types = ['doc', 'docx'];
                break;
            case 'zips':
                $types = ['zip', 'rar'];
                break;
            default:
                $types = [$rawType];
        }

        return $types;
    }

    public static function parseFileSizeLimit($rawSizeLimit): int
    {
        //bytes
        if (is_numeric($rawSizeLimit)) {
            return $rawSizeLimit;
        }

        //kb
        if (preg_match("/^(\d*)kb$/i", $rawSizeLimit, $matches)) {
            return $matches[1] * 1024;
        }

        //mb
        if (preg_match("/^(\d*)mb$/i", $rawSizeLimit, $matches)) {
            return $matches[1] * 1024 * 1024;
        }

        throw new \Exception('Can not parse param fileSizeLimit');
    }

    public static function getReadableSizeLimit($sizeLimit): string
    {
        if ($sizeLimit > 1024 * 1024) {
            return round($sizeLimit / 1024 / 1024) . 'Mb(兆)';
        }

        return round($sizeLimit / 1024) . 'Kb';
    }
}