<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Framework\App\Resource as AppResource;
use Magento\SalesSequence\Model\Sequence\SequenceManager;
use Magento\Sales\Model\Resource\Attribute;
use Magento\Sales\Model\Resource\EntityAbstract as SalesResource;
use Magento\Sales\Model\Resource\EntitySnapshot;
use Magento\Sales\Model\Resource\Order\Creditmemo\Grid as CreditmemoGrid;
use Magento\Sales\Model\Spi\CreditmemoResourceInterface;

/**
 * Flat sales order creditmemo resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Creditmemo extends SalesResource implements CreditmemoResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_creditmemo', 'entity_id');
    }

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param Attribute $attribute
     * @param SequenceManager $sequenceManager
     * @param CreditmemoGrid $gridAggregator
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        Attribute $attribute,
        SequenceManager $sequenceManager,
        EntitySnapshot $entitySnapshot,
        CreditmemoGrid $gridAggregator,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $attribute, $sequenceManager, $entitySnapshot, $resourcePrefix, $gridAggregator);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setBillingAddressId($object->getOrder()->getBillingAddress()->getId());
        }

        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function processRelations(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $item->save();
            }
        }

        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $comment->save();
            }
        }
        return parent::_afterSave($object);
    }
}
