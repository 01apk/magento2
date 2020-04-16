<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Wishlist\Observer\CustomerLogout as Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerLogoutTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWishlistItemCount', 'isLoggedIn', 'getCustomerId'])
            ->getMock();

        $this->observer = new Observer(
            $this->customerSession
        );
    }

    public function testExecute()
    {
        $event = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->customerSession->expects($this->once())
            ->method('setWishlistItemCount')
            ->with($this->equalTo(0));

        $this->observer->execute($event);
    }
}
