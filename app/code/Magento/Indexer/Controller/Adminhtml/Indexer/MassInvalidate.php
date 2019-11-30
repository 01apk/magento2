<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class MassInvalidate extends \Magento\Indexer\Controller\Adminhtml\Indexer implements HttpPostActionInterface
{
    /**
     * Turn mview on for the given indexers
     *
     * @return void
     */
    public function execute()
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addError(__('Please select indexers.'));
        } else {
            try {
                foreach ($indexerIds as $indexerId) {
                    /** @var \Magento\Framework\Indexer\IndexerInterface $model */
                    $model = $this->_objectManager->get(
                        \Magento\Framework\Indexer\IndexerRegistry::class
                    )->get($indexerId);
                    $model->invalidate();
                }
                $this->messageManager->addSuccess(
                    __('%1 indexer(s) were invalidated.', count($indexerIds))
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't invalidate indexer(s) because of an error.")
                );
            }
        }
        $this->_redirect('*/*/list');
    }
}
