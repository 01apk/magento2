<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class Nested extends AbstractExtensibleObject
{
    /**
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\Simple
     */
    public function getDetails()
    {
        return $this->_get('details');
    }

    /**
     * @param \Magento\Webapi\Service\Entity\Simple $details
     * @return $this
     */
    public function setDetails($details)
    {
        return $this->setData('details', $details);
    }
}
