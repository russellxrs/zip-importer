<?php


namespace Russellxrs\ZipImporter;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

class ZipImporter
{
    protected array $userFunctions = [];

    protected Zipper $zipper;

    protected Rule $rule;

    protected Transformer $transformer;

    protected ExcelReader $excelReader;

    protected ValidatorFacade $validatorFacade;

    protected Validator $validator;

    protected FileValidator $fileValidator;

    protected array $errors = [];

    public function __construct(string $zipPath)
    {
        $this->zipper = Zipper::load($zipPath);
    }

    public function read()
    {
        $this->excelReader = ExcelReader::load($this->zipper->sourceDir . '/example.xls');

        $this->transformer = Transformer::load(
            $this->excelReader->read(),
            $this->rule->getFields(),
            $this->userFunctions
        );
    }

    public function validate(ValidatorFacade $validatorFacade)
    {
        $this->validatorFacade = $validatorFacade;

       foreach($this->transformer->getAssocData() as $columnIndex => $row){
           if($this->dataValid($row) && $this->fileValid($row)){
               continue;
           }

           $this->errors[] = [
               'column' => $columnIndex,
               'messages' => $this->validator->messages()->merge(
                   $this->fileValidator->messages()
               )
           ];
       }
    }

    protected function dataValid($row) : bool{
        $this->validator = call_user_func([$this->validatorFacade, 'make'], [$row, $this->rule->getLaravelRules()]);

        return $this->validator->passes();
    }

    protected function fileValid($row) : bool{
        $rules = $this->rule->getFileRules();

        if(!$rules){
            return true;
        }

        $this->fileValidator = FileValidator::make($row, $rules, $this->zipper->sourceDir);

        return $this->fileValidator->passes();
    }

    public function setRules(array $rules) : self
    {
        $this->rule = Rule::load($rules);

        return $this;
    }

    public function setUserFunctions(array $functions) : self
    {
        $this->userFunctions = $functions;

        return $this;
    }

    public function errors() : array
    {
        return $this->errors;
    }

    public function passes() : bool
    {
        return count($this->errors) === 0;
    }
}