<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;

class AssignPost extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save status assignment to state
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $state = $this->getRequest()->getParam('state');
            $isDefault = $this->getRequest()->getParam('is_default');
            $visibleOnFront = $this->getRequest()->getParam('visible_on_front');
            $status = $this->_initStatus();
            if ($status && $status->getStatus()) {
                try {
                    $status->assignState($state, $isDefault, $visibleOnFront);
                    $this->messageManager->addSuccess(__('You have assigned the order status.'));
                    return $resultRedirect->setPath('sales/*/');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('An error occurred while assigning order status. Status has not been assigned.')
                    );
                }
            } else {
                $this->messageManager->addError(__('We can\'t find this order status.'));
            }
            return $resultRedirect->setPath('sales/*/assign');
        }
        return $resultRedirect->setPath('sales/*/');
    }
}
