<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Rss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RssTest extends TestCase
{
    /**
     * @var Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerInterface;

    /**
     * @var MockObject
     */
    protected $reviewFactory;

    protected function setUp(): void
    {
        $this->managerInterface = $this->createMock(ManagerInterface::class);
        $this->reviewFactory = $this->createPartialMock(ReviewFactory::class, ['create']);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            Rss::class,
            [
                'eventManager' => $this->managerInterface,
                'reviewFactory' => $this->reviewFactory
            ]
        );
    }

    public function testGetProductCollection()
    {
        $reviewModel = $this->createPartialMock(Review::class, [
                '__wakeUp',
                'getProductCollection'
            ]);
        $productCollection = $this->createPartialMock(
            Collection::class,
            [
                'addStatusFilter',
                'addAttributeToSelect',
                'setDateOrder'
            ]
        );
        $reviewModel->expects($this->once())->method('getProductCollection')
            ->will($this->returnValue($productCollection));
        $this->reviewFactory->expects($this->once())->method('create')->will($this->returnValue($reviewModel));
        $productCollection->expects($this->once())->method('addStatusFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('setDateOrder')->will($this->returnSelf());
        $this->managerInterface->expects($this->once())->method('dispatch')->will($this->returnSelf());
        $this->assertEquals($productCollection, $this->rss->getProductCollection());
    }
}
