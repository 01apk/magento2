<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

class Logout extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Administrator logout action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_auth->logout();
        $this->messageManager->addSuccess(__('You have logged out.'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}
