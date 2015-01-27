<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;

/**
 * Class Link
 * Multishipping cart link
 *
 */
class Link extends Block
{
    /**
     * Press 'Proceed to Checkout' link
     */
    public function multipleAddressesCheckout()
    {
        $this->_rootElement->click();
    }
}
