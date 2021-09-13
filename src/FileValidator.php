<?php


namespace Russellxrs\ZipImporter;


use http\Message;
use Illuminate\Support\MessageBag;
use Russellxrs\ZipImporter\Exceptions\RulesFormatException;

class FileValidator
{
    protected string $dest;

    protected array $data;

    protected array $rules;

    protected MessageBag $messages;

    public function __construct($data, $rules, $dest)
    {
        $this->data = $data;

        $this->rules = $rules;

        $this->dest = $dest;
    }

    public static function make($data, $rules, $dest): self
    {
        return new static($data, $rules, $dest);
    }

    public function validate() : bool
    {
        //todo:: return validated value;
        return $this->passes();
    }

    public function passes() : bool{
        $this->messages = new MessageBag();

        foreach ($this->rules as $attribute => $rule) {
            $fileName = $this->data[$attribute];

            [$folder, $types, $sizeLimit] = self::parseRule($rule);

            $matches = glob($this->dest . '/' . $folder . '/' . $fileName . '*');

            if (!$matches) {
                $this->messages->add($attribute, '没有找到对应文件');
                continue;
            }

            if (count($matches) > 1) {
                $this->messages->add($attribute, '存在多个重名文件，请检查');
                continue;
            }

            $targetFile = $matches[0];

            if (count($matches) === 1) {
                $pathInfo = pathinfo($targetFile);

                $fileExtension = $pathInfo['extension'];

                if (!in_array($fileExtension, $types)) {
                    $this->messages->add($attribute, '文件格式不正确，必须是' . join('、', $types));
                }

                if (filesize($targetFile) > $sizeLimit) {
                    $this->messages->add($attribute, '文件超出' . self::getReadableSizeLimit($sizeLimit) . ',请压缩后上传');
                }
            }
        }

        return $this->messages->isEmpty();
    }

    public function fails() : bool{
        return ! $this->passes();
    }

    public function messages() : MessageBag
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    public function errors() : MessageBag
    {
        return $this->messages();
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