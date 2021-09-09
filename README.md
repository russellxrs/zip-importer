#Zip Importer

##功能

帮助校验zip文件内的excel数据、文件

##依赖环境和包

-  php 7.4
- laravel 6 以上（需要依赖validate facade）


##使用方法

$zipImporter = new \Russellxrs\ZipImporter\ZipImporter();

$zipImporter->setPath($zipPath)->setRules($rules)->setUserFunc([])->validate($facade);

$data = $zipImporter->validated();

$imageFile = $zipImporter->getFilePaths('photo');


- 中文header
- 需要校验的文件夹 [文件夹的名称、对应的字段、每个文件限制的大小] \ Transformer里拿数据


$rules = [
    'id_num|身份证' => 'required|max:20|checkFile<照片:image|2M>',
];




