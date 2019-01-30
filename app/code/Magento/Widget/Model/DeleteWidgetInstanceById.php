<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\ResourceModel\Widget\Instance as InstanceResourceModel;
use Magento\Widget\Model\Widget\InstanceFactory as WidgetInstanceFactory;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

/**
 * Class DeleteWidgetInstanceById
 */
class DeleteWidgetInstanceById
{
    /**
     * @var InstanceResourceModel
     */
    private $resourceModel;

    /**
     * @var WidgetInstanceFactory|WidgetInstance
     */
    private $instanceFactory;

    /**
     * @param InstanceResourceModel $resourceModel
     * @param WidgetInstanceFactory $instanceFactory
     */
    public function __construct(
        InstanceResourceModel $resourceModel,
        WidgetInstanceFactory $instanceFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->instanceFactory = $instanceFactory;
    }

    /**
     * Delete widget instance by given instance ID
     *
     * @param int $instanceId
     * @return void
     * @throws \Exception
     */
    public function execute(int $instanceId)
    {
        $model = $this->getWidgetById($instanceId);

        $this->resourceModel->delete($model);
    }

    /**
     * @param int $instanceId
     * @return WidgetInstance
     * @throws NoSuchEntityException
     */
    private function getWidgetById(int $instanceId)
    {
        /** @var WidgetInstance $widgetInstance */
        $widgetInstance = $this->instanceFactory->create();

        $this->resourceModel->load($widgetInstance, $instanceId);

        if (!$widgetInstance->getId()) {
            throw NoSuchEntityException::singleField('instance_id', $instanceId);
        }

        return $widgetInstance;
    }
}
