<?php


namespace Russellxrs\ZipImporter;

use Illuminate\Validation\Validator;

class ZipImporter
{
    protected array $userFunctions = [];

    protected Zipper $zipper;

    protected Rule $rule;

    protected Transformer $transformer;

    protected ExcelReader $excelReader;

    protected string $validatorFacade;

    protected Validator $validator;

    protected FileValidator $fileValidator;

    protected array $validatedFiles = [];

    protected array $errors = [];

    public function __construct(string $zipPath, array $importFileExt=[])
    {
        $this->zipper = Zipper::load($zipPath, $importFileExt);
    }

    public function __destruct()
    {
        $this->zipper->delTmpFolder();
    }

    public function read()
    {
        $this->excelReader = ExcelReader::load($this->zipper->importFilePath());

        $this->transformer = Transformer::load(
            $this->excelReader->read(),
            $this->rule->getFields(),
            $this->userFunctions
        );
    }

    public function validate(string $validatorFacade)
    {
        $this->validatorFacade = $validatorFacade;

        $this->read();

        foreach ($this->transformer->getAssocData() as $columnIndex => $row) {
            if ($this->dataValid($row) && $this->fileValid($row)) {
                $this->validatedFiles = array_merge_recursive($this->validatedFiles, $this->fileValidator->validated());
                continue;
            }

            $this->errors[] = [
                'column' => $columnIndex,
                'messages' => $this->validator->messages()->merge(
                    isset($this->fileValidator) ? $this->fileValidator->messages() : []
                )
            ];
        }
    }

    protected function dataValid($row): bool
    {
        $this->validator = call_user_func([$this->validatorFacade, 'make'], $row, $this->rule->getLaravelRules());

        return $this->validator->passes();
    }

    protected function fileValid($row): bool
    {
        $rules = $this->rule->getFileRules();

        if (!$rules) {
            return true;
        }

        $this->fileValidator = FileValidator::make($row, $rules, $this->zipper->sourceDir);

        return $this->fileValidator->passes();
    }

    public function setRules(array $rules): self
    {
        $this->rule = Rule::load($rules);

        return $this;
    }

    public function setUserFunctions(array $functions): self
    {
        $this->userFunctions = $functions;

        return $this;
    }

    public function validatedData(): array
    {
        return collect($this->transformer->getAssocData())->filter(function($value, $index){
            return !collect($this->errors())->contains('column', $index);
        })->values()->toArray();
    }

    public function validatedFiles(): array
    {
        return $this->validatedFiles;
    }

    public function assocData(): array
    {
        return $this->transformer->getAssocData();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function passes(): bool
    {
        return count($this->errors) === 0;
    }
}