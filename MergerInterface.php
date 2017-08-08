<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface \Magento\Framework\MessageQueue\MergerInterface
 *
 */
interface MergerInterface
{
    /**
     * @param object[] $messages
     * @return object[]
     */
    public function merge(array $messages);
}
