<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\ResourceModel;

/**
 * Resource model for message status.
 */
class MessageStatus extends \Magento\Framework\Model\ModelResource\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('queue_message_status', 'id');
    }
}
