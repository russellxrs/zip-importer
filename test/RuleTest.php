<?php

namespace Test;

use Russellxrs\ZipImporter\Rule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    public array $rules;

    public function setup() : void
    {
        $this->rules = [
            'user|用户名' => 'checkFile<照片:image|20kb>|required|max:20',
            'sex|性别' => 'required'
        ];
    }

    /** @test */
    function it_parse_rules_to_laravel_rules(){
        $this->assertEquals(['user' => 'required|max:20', 'sex' => 'required'], Rule::load($this->rules)->getLaravelRules());
    }

    /** @test */
    function it_parse_rules_to_file_check_rules(){
        $this->assertEquals([['field' => 'user', 'folder' => '照片', 'type' => 'image', 'sizeLimit' => '20kb']], Rule::load($this->rules)->getFileRules());
    }

    /** @test */
    function it_parse_headers(){
        $this->assertEquals(['用户名', '性别'], Rule::load($this->rules)->getHeaders());
    }
    
    /** @test */
    function it_parse_fields(){
        $this->assertEquals(['user', 'sex'], Rule::load($this->rules)->getFields());
    }
}
