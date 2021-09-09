<?php


namespace Russellxrs\ZipImporter;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

class ZipImporter
{
    protected string $zipPath;

    protected array $userFunctions = [];

    protected Zipper $zipper;

    protected Rule $rule;

    protected Transformer $transformer;

    protected ExcelReader $excelReader;

    protected ValidatorFacade $validatorFacade;

    protected Validator $validator;

    protected FileValidator $fileValidator;

    protected array $illegalRowsIndex = [];

    protected array $legalRowsIndex = [];

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

       foreach($this->transformer->getAssocData() as $row){
           $this->checkData($row);

           $this->checkFile($row);
       }
    }

    protected function checkData($row){
        $this->validator = call_user_func([$this->validatorFacade, 'make'], [$row, $this->rule->getLaravelRules()]);

        return $this->validator->passes();

//        if($this->validator->fails()){
//            $this->illegalRowsIndex[] = $rowIndex;
//
//            $errorKeys = $this->validator->errors()->keys();
//
//            foreach($errorKeys as $errorKey){
//                $this->errors[] = [
//                    'rowIndex' => $rowIndex,
//                    'colIndex' => array_search($errorKey, $this->rule->getFields()),
//                    'message' => $this->validator->errors()->get($errorKey)
//                ];
//            }
//        }
    }

    protected function checkFile($row){
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