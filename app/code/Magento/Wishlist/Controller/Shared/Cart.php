<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Controller\Shared;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection as OptionCollection;

/**
 * Wishlist Cart Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart implements HttpGetActionInterface
{
    /**
     * @var CustomerCart
     */
    private $cart;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    public function __construct(
        CustomerCart $cart,
        OptionFactory $optionFactory,
        ItemFactory $itemFactory,
        CartHelper $cartHelper,
        Escaper $escaper,
        RequestInterface $request,
        RedirectInterface $redirect,
        MessageManagerInterface $messageManager,
        ResultFactory $resultFactory
    ) {
        $this->cart = $cart;
        $this->optionFactory = $optionFactory;
        $this->itemFactory = $itemFactory;
        $this->cartHelper = $cartHelper;
        $this->escaper = $escaper;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Add shared wishlist item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     * @inheritDoc
     */
    public function execute()
    {
        $itemId = (int)$this->request->getParam('item');

        /* @var $item Item */
        $item = $this->itemFactory->create()
            ->load($itemId);

        $redirectUrl = $this->redirect->getRefererUrl();

        try {
            /** @var OptionCollection $options */
            $options = $this->optionFactory->create()
                ->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));
            $item->addToCart($this->cart);

            $this->cart->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccessMessage($message);
            }

            if ($this->cartHelper->getShouldRedirectToCart()) {
                $redirectUrl = $this->cartHelper->getCartUrl();
            }
        } catch (ProductException $e) {
            $this->messageManager->addErrorMessage(__('This product(s) is out of stock.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            $redirectUrl = $item->getProductUrl();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add the item to the cart right now.'));
        }
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
