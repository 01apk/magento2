<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Elasticsearch\Model\ResourceModel\Engine;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\ResourceModel\Engine
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexScopeResolver;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $resource->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));

        $this->catalogProductVisibility = $this->getMockBuilder('\Magento\Catalog\Model\Product\Visibility')
            ->disableOriginalConstructor()
            ->setMethods(['getVisibleInSiteIds'])
            ->getMock();

        $this->indexScopeResolver = $this->getMockBuilder('\Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver')
            ->disableOriginalConstructor()
            ->setMethods(['getVisibleInSiteIds'])
            ->getMock();

        $this->model = new Engine(
            $this->catalogProductVisibility,
            $this->indexScopeResolver
        );
    }

    /**
     * @param null|string $expected
     * @param array $data
     * @dataProvider prepareEntityIndexDataProvider
     */
    public function testPrepareEntityIndex($expected, array $data)
    {
        $this->assertEquals($expected, $this->model->prepareEntityIndex($data['index'], $data['separator']));
    }

    /**
     *  Test allowAdvancedIndex method
     */
    public function testAllowAdvancedIndex()
    {
        $this->assertEquals(false, $this->model->allowAdvancedIndex());
    }

    /**
     *  Test isAvailable method
     */
    public function testIsAvailable()
    {
        $this->assertEquals(true, $this->model->isAvailable());
    }

    /**
     *  Test getAllowedVisibility method
     *  Will return getVisibleInSiteIds array
     */
    public function testGetAllowedVisibility()
    {
        $this->catalogProductVisibility->expects($this->once())
            ->method('getVisibleInSiteIds')
            ->willReturn([3, 2, 4]);

        $this->assertEquals([3, 2, 4], $this->model->getAllowedVisibility());
    }

    /**
     *  Test processAttributeValue method
     */
    public function testProcessAttributeValue()
    {
        $this->assertEquals(1, $this->model->processAttributeValue('attribute', 1));
    }

    /**
     * @return array
     */
    public function prepareEntityIndexDataProvider()
    {
        return [
            [
                [],
                [
                    'index' => [],
                    'separator' => ' ',
                ],
            ],
        ];
    }
}
