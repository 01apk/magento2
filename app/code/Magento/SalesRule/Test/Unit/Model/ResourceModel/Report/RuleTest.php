<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Report;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\FlagFactory;
use Magento\SalesRule\Model\ResourceModel\Report\Rule;
use Magento\SalesRule\Model\ResourceModel\Report\Rule\CreatedatFactory;
use Magento\SalesRule\Model\ResourceModel\Report\Rule\UpdatedatFactory;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * Test table name
     */
    const TABLE_NAME = 'test';

    /**
     * List of test rules;
     *
     * @var array
     */
    protected $_rules = [
        ['rule_name' => 'test1'],
        ['rule_name' => 'test2'],
        ['rule_name' => 'test3'],
    ];

    public function testGetUniqRulesNamesList()
    {
        $dbAdapterMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['_connect', 'quote'])
            ->disableOriginalConstructor()
            ->getMock();
        $dbAdapterMock
            ->expects($this->any())
            ->method('quote')
            ->willReturnCallback(
                function ($value) {
                    return "'$value'";
                }
            );

        $selectRenderer = $this->getMockBuilder(SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(Select::class)
            ->setMethods(['from'])
            ->setConstructorArgs([$dbAdapterMock, $selectRenderer])
            ->getMock();
        $select->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            self::TABLE_NAME,
            $this->isInstanceOf('Zend_Db_Expr')
        )->will(
            $this->returnValue($select)
        );

        $connectionMock = $this->createPartialMock(
            Mysql::class,
            ['select', 'fetchAll']
        );
        $connectionMock->expects($this->once())->method('select')->will($this->returnValue($select));
        $connectionMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select
        )->will(
            $this->returnCallback([$this, 'fetchAllCallback'])
        );

        $resourceMock = $this->createMock(ResourceConnection::class);
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $resourceMock->expects($this->once())->method('getTableName')->will($this->returnValue(self::TABLE_NAME));

        $flagFactory = $this->createMock(FlagFactory::class);
        $createdatFactoryMock = $this->createPartialMock(
            CreatedatFactory::class,
            ['create']
        );
        $updatedatFactoryMock = $this->createPartialMock(
            UpdatedatFactory::class,
            ['create']
        );

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Rule::class,
            [
                'resource' => $resourceMock,
                'reportsFlagFactory' => $flagFactory,
                'createdatFactory' => $createdatFactoryMock,
                'updatedatFactory' => $updatedatFactoryMock
            ]
        );

        $expectedRuleNames = [];
        foreach ($this->_rules as $rule) {
            $expectedRuleNames[] = $rule['rule_name'];
        }
        $this->assertEquals($expectedRuleNames, $model->getUniqRulesNamesList());
    }

    /**
     * Check structure of sql query
     *
     * @param Select $select
     * @return array
     */
    public function fetchAllCallback(Select $select)
    {
        $whereParts = $select->getPart(Select::WHERE);
        $this->assertCount(2, $whereParts);
        $this->assertContains("rule_name IS NOT NULL", $whereParts[0]);
        $this->assertContains("rule_name <> ''", $whereParts[1]);

        $orderParts = $select->getPart(Select::ORDER);
        $this->assertCount(1, $orderParts);
        $expectedOrderParts = ['rule_name', 'ASC'];
        $this->assertEquals($expectedOrderParts, $orderParts[0]);

        return $this->_rules;
    }
}
