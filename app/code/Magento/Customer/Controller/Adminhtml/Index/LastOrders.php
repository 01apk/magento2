<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class LastOrders extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer last orders grid for ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultCustomerTitle($resultPage);
        return $resultPage;
    }
}
