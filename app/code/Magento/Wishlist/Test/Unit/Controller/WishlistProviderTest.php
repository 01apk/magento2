<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use PHPUnit\Framework\TestCase;

class WishlistProviderTest extends TestCase
{
    /**
     * @var WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->request = $this->createMock(RequestInterface::class);

        $this->wishlistFactory = $this->createPartialMock(WishlistFactory::class, ['create']);

        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerId']);

        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->wishlistProvider = $objectManager->getObject(
            WishlistProvider::class,
            [
                'request' => $this->request,
                'wishlistFactory' => $this->wishlistFactory,
                'customerSession' => $this->customerSession,
                'messageManager' => $this->messageManager
            ]
        );
    }

    public function testGetWishlist()
    {
        $wishlist = $this->createMock(Wishlist::class);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'getId', 'getCustomerId', '__wakeup']
        );
        $wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->will($this->returnSelf());
        $wishlist->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdAndCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup']
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdWithoutCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup']
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->assertEquals(false, $this->wishlistProvider->getWishlist());
    }
}
