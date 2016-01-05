<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Builder
     */
    protected $model;
    /**
     * @var Range|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $range;

    /**
     * @var Term|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $term;

    /**
     * @var Wildcard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wildcard;

    /**
     * Set up test environment.
     *
     * @return void
     */
    public function setUp()
    {
        $this->range = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range')
            ->disableOriginalConstructor()
            ->getMock();
        $this->term = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term')
            ->disableOriginalConstructor()
            ->getMock();
        $this->wildcard = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard')
            ->disableOriginalConstructor()
            ->getMock();

        $this->term->expects($this->any())
            ->method('buildFilter')
            ->willReturn([]);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Filter\Builder',
            [
                'range' => $this->range,
                'term' => $this->term,
                'wildcard' => $this->wildcard
            ]
        );
    }

    /**
     * Test build() method failure
     * @expectedException \InvalidArgumentException
     */
    public function testBuildFailure()
    {
        $filter = $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn('unknown');

        $this->model->build($filter, 'must');
    }

    /**
     * Test build() method
     * @param string $filterMock
     * @param string $filterType
     * @dataProvider buildDataProvider
     */
    public function testBuild($filterMock, $filterType)
    {
        $filter = $this->getMockBuilder($filterMock)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn($filterType);
        $childFilter = $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $childFilter->expects($this->any())
            ->method('getType')
            ->willReturn('termFilter');
        $filter->expects($this->any())
            ->method('getMust')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getShould')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getMustNot')
            ->willReturn([$childFilter]);

        $this->model->build($filter, 'must');
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [
                'Magento\Framework\Search\Request\FilterInterface',
                'termFilter'
            ],
            [
                'Magento\Framework\Search\Request\Filter\BoolExpression',
                'boolFilter'
            ],
        ];
    }
}
