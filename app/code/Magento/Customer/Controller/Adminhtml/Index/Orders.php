<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class Orders extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer orders grid
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
