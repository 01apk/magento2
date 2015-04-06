<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\PrivateData\Section;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;

/**
 * Customer section
 */
class Customer implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'username' => $this->currentCustomer->getCustomerId()
                ? $this->customerViewHelper->getCustomerName($this->currentCustomer->getCustomer())
                : ''
        ];
    }
}
