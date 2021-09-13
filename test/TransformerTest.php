<?php


namespace Test;

use PHPUnit\Framework\TestCase;
use Russellxrs\ZipImporter\Exceptions\FieldsNotMatchException;
use Russellxrs\ZipImporter\Transformer;

class TransformerTest extends TestCase
{
    /** @test */
    function it_can_transfer_index_array_to_assoc()
    {
        $array = [
            ['1a', '1b', '1c', '1d', '1e']
        ];

        $fields = ['field_a', 'field_b', 'field_c', 'field_d', 'field_e'];

        $expected = [
            [
                'field_a' => '1a',
                'field_b' => '1b',
                'field_c' => '1c',
                'field_d' => '1d',
                'field_e' => '1e'
            ]
        ];

        $this->assertEquals(
            $expected,
            Transformer::load($array, $fields)->getAssocData()
        );
    }

    /** @test */
    function it_throws_an_exception_if_fields_not_match(){
        $array = [
            ['1a', '1b', '1c', '1d', '1e']
        ];

        $fields = ['field_a'];

        $this->expectException(FieldsNotMatchException::class);

        Transformer::load($array, $fields);
    }

    /** @test */
    function it_can_reset_value_by_user_func_array(){
        $array = [['1a']];

        $fields = ['field_a'];

        $funcArray = [
          'field_a' => function($value){
                return  $value . ' has been changed';
          }
        ];

        $expected = [
            ['field_a' => '1a has been changed']
        ];

        $this->assertEquals($expected, Transformer::load($array, $fields, $funcArray)->getAssocData());
    }
    
//    /** @test */
//    function it_can_return_specific_column(){
//        $array = [['1a', '1b', '1c'],['2a', '2b', '2c'], ['3a', '3d', '3c']];
//
//        $fields = ['field_a', 'field_b', 'field_c'];
//
//        $column = 3;
//
//        $expected = ['1c', '2c', '3c'];
//
//        $this->assertEquals($expected, Transformer::load($array, $fields)->getColumn(3));
//    }
}