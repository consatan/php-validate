<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-11
 * Time: 00:10
 */

namespace Inhere\ValidateTest\Filter;

use Inhere\Validate\Filter\Filtration;
use PHPUnit\Framework\TestCase;

/**
 * Class FiltrationTest
 * @package Inhere\ValidateTest\Filter
 */
class FiltrationTest extends TestCase
{
    private $data = [
        'name'    => ' tom ',
        'status'  => ' 23 ',
        'word'    => 'word',
        'toLower' => 'WORD',
        'title'   => 'helloWorld',
    ];

    public function testBasic(): void
    {
        $fl = Filtration::make($this->data);

        $this->assertFalse($fl->has('age'));

        $fl->load([
            'age' => '34',
        ]);

        $this->assertTrue($fl->has('age'));
        $this->assertSame(34, $fl->get('age', 'intval'));
        $this->assertSame(23, $fl->get('status', 'trim|int'));
        $this->assertNull($fl->get('not-exists'));
    }

    public function testUserFilters(): void
    {
        $fl = Filtration::make($this->data);
        $fl->clearFilters();
        $fl->addFilters([
            'name1' => function () {
            },
            'name2' => function () {
            },
            ''      => function () {
            },
        ]);

        $this->assertCount(2, $fl->getFilters());

        $this->assertNotEmpty($fl->getFilter('name2'));
        $this->assertEmpty($fl->getFilter('name3'));

        $fl->addFilter('new1', function () {
        });
        $this->assertNotEmpty($fl->getFilter('new1'));

        $fl->delFilter('name1');
        $this->assertEmpty($fl->getFilter('name1'));

        $fl->clearFilters();
        $this->assertCount(0, $fl->getFilters());
    }

    public function testFiltering(): void
    {

        $rules = [
            ['name', 'string|trim'],
            ['status', 'trim|int'],
            ['word', 'string|trim|upper'],
            ['toLower', 'lower'],
            [
                'title',
                [
                    'string',
                    'snake' => ['-'],
                    'ucfirst',
                ]
            ],
        ];

        $fl = Filtration::make($this->data);
        $fl->setRules($rules);

        // get cleaned data
        $cleaned = $fl->filtering();
        $this->assertSame('tom', $cleaned['name']);
        $this->assertSame(' tom ', $fl->get('name'));
        $this->assertSame('default', $fl->get('not-exist', null, 'default'));
        $this->assertSame('TOM', $fl->get('name', 'trim|upper'));

        $fl->reset(true);

        $this->assertEmpty($fl->all());
        $this->assertEmpty($fl->getData());
        $this->assertEmpty($fl->getRules());
    }
}
