# Zip Importer

## 功能

校验zip文件内的excel数据和文件

## 依赖

- ^php 7.4
- ^laravel 6


## 安装

````
composer require russellxrs/zip-importer
````

## 使用

### 示例

````
$zipImporter = new \Russellxrs\ZipImporter\ZipImporter($zipPath);

$zipImporter->setRules($rules)
    ->setUserFunc([
        'id' => function($id){
            return $id + 1;
        }
    ])
    ->validate($validatorFacade);

$data = $zipImporter->validatedData();

$file = $zipImporter->validatedFiles();

$errors = $zipImporter->errors();
````

### 规则定义

````
$rules = [
    'id|编号' => 'required',
    'id_num|身份证' => 'required|checkFile<images:txt|2Mb>'
];
````

* 键名 : 
    * 格式：字段名|字段中文名

* 键值 : 
    * 和laravel的验证规则格式一致
    * checkFile规则是检查文件的验证规则， 格式为： checkFile<文件夹名称:文件类型|文件大小>
    * checkFile规则的文件大小可以直接用kb,mb表示，如果是纯数字，则单位为byte。


### 方法

#### setUserFunc 
**用途:** 用于修改excel文件内读取的数据
**参数:**一个回调函数数组
**示例:** 
```` 
$zipImporter->setUserFunc([
    'id' => function($id){
        return $id + 1;
    }
])
````

#### setRules
用于设置校验规则

#### validate
校验

#### validatedData
合法数据

#### validatedFiles
合法文件

#### assocData
关联数组

#### errors
校验错误


