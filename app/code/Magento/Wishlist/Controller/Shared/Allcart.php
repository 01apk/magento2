<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Controller\Shared;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Model\ItemCarrier;

class Allcart extends Action implements HttpGetActionInterface
{
    /**
     * @var ItemCarrier
     */
    private $itemCarrier;

    /**
     * @var WishlistProvider
     */
    private $wishlistProvider;

    /**
     * @param Context $context
     * @param ItemCarrier $itemCarrier
     * @param WishlistProvider $wishlistProvider
     */
    public function __construct(
        Context $context,
        ItemCarrier $itemCarrier,
        WishlistProvider $wishlistProvider
    ) {
        $this->itemCarrier = $itemCarrier;
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * {@inheritDoc}
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
