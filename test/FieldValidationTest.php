<?php

namespace Inhere\ValidateTest;

use Inhere\Validate\FieldValidation;
use Inhere\ValidateTest\Sample\FieldSample;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class FieldValidationTest
 * @package Inhere\ValidateTest
 */
class FieldValidationTest extends TestCase
{
    public $data = [
        // 'userId' => 234,
        'userId'      => 'is not an integer',
        'tagId'       => '234535',
        // 'freeTime' => '1456767657', // filed not exists
        'note'        => '',
        'name'        => 'Ajohn',
        'status'      => 2,
        'existsField' => 'test',
        'passwd'      => 'password',
        'repasswd'    => 'repassword',
        'insertTime'  => '1456767657',
        'goods'       => [
            'apple' => 34,
            'pear'  => 50,
        ],
    ];

    public function testRuleCollectError(): void
    {
        $rv = FieldValidation::make(['name' => 'inhere'], [
            []
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame('Please setting the field(string) to wait validate! position: rule[0]', $e->getMessage());
        }

        $rv = FieldValidation::make(['name' => 'inhere'], [
            ['name']
        ]);
        try {
            $rv->validate();
        } catch (Throwable $e) {
            $this->assertSame('The field validators must be is a validator name(s) string! position: rule[1]', $e->getMessage());
        }
    }

    public function testValidateField(): void
    {
        $rules = [
            ['freeTime', 'required'],
            ['userId', 'required|int'],
            ['tagId', 'size:0,50'],
            ['status', 'enum:1,2'],
            ['goods.pear', 'max:30'],
        ];

        $v = FieldValidation::make($this->data, $rules);
        $v->setMessages([
            'freeTime.required' => 'freeTime is required!!!!'
        ])
          ->validate([], false);

        $this->assertFalse($v->isOk());
        $this->assertTrue($v->failed());

        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);
        $this->assertSame('freeTime is required!!!!', $v->getErrors('freeTime')[0]);

        $v = FieldValidation::check($this->data, [
            ['goods.pear', 'required|int|min:30|max:60']
        ]);
        $this->assertTrue($v->isOk());

        $v = FieldValidation::check($this->data, [
            ['userId', 'required|int'],
            ['userId', 'min:1'],
        ]);

        $this->assertFalse($v->isOk());
        $errors = $v->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);

        $v = FieldValidation::check([
            'title' => 'hello',
        ], [
            ['title', 'required|string:2,8']
        ]);
        $this->assertTrue($v->isOk());

        $v = FieldValidation::check([
            'title' => 'hello',
        ], [
            ['title', 'required|string:1,3']
        ]);
        $this->assertTrue($v->isFail());
        $this->assertSame(
            'title must be a string and length range must be 1 ~ 3',
            $v->firstError()
        );
    }

    public function testOnScene(): void
    {
        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '1234',
        ];

        $v = FieldValidation::make($data, [
            ['user', 'required|string', 'on' => 's1'],
            ['code', 'required|int', 'filter' => 'int', 'on' => 's2'],
        ]);

        $v->atScene('s1')->validate();

        $this->assertCount(1, $v->getUsedRules());
    }

    public function testScenarios(): void
    {
        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '1234',
        ];

        $v = FieldSample::quick($data, 'create')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());

        $data = [
            'user' => 'inhere',
            'pwd'  => '123456',
            'code' => '12345',
        ];

        $v = FieldSample::quick($data, 'create')->validate();
        $this->assertFalse($v->isOk());
        $this->assertEquals('code length must is 4', $v->firstError());

        $v = FieldSample::quick($data, 'update')->validate();
        $this->assertTrue($v->isOk());
        $this->assertEmpty($v->getErrors());
    }

    public function testEachValidator(): void
    {
        $data = [
            'employees' => [
                [
                    'name' => 'aaa',
                    'manage' => false,
                    'age' => 28,
                ],
                [
                    'name' => 'bbb',
                    'manage' => true,
                    'age' => 32,
                ],
                [
                    'name' => 'ccc',
                    'manage' => false,
                    'age' => 24,
                ]
            ],
        ];

        $v = FieldValidation::check($data, [
            ['employees.*.age', 'each|required|int|min:18|max:35']
        ]);
        $this->assertTrue($v->isOk());
    }
}
