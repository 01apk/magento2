<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\AddStockStatusToCollection;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddStockStatusToCollectionTest extends TestCase
{
    /**
     * @var AddStockStatusToCollection
     */
    protected $plugin;

    /**
     * @var Stock|MockObject
     */
    protected $stockHelper;

    /**
     * @var EngineResolverInterface|MockObject
     */
    private $engineResolver;

    protected function setUp(): void
    {
        $this->stockHelper = $this->createMock(Stock::class);
        $this->engineResolver = $this->getMockBuilder(EngineResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentSearchEngine'])
            ->getMockForAbstractClass();

        $this->plugin = (new ObjectManager($this))->getObject(
            AddStockStatusToCollection::class,
            [
                'stockHelper' => $this->stockHelper,
                'engineResolver' => $this->engineResolver
            ]
        );
    }

    public function testAddStockStatusToCollection()
    {
        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->engineResolver->expects($this->any())
            ->method('getCurrentSearchEngine')
            ->willReturn('mysql');

        $this->stockHelper->expects($this->once())
            ->method('addIsInStockFilterToCollection')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->plugin->beforeLoad($productCollection);
    }
}
