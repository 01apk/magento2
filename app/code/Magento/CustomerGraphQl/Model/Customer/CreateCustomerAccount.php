<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create new customer account
 */
class CreateCustomerAccount
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ChangeSubscriptionStatus
     */
    private $changeSubscriptionStatus;

    /**
     * @var GetAllowedCustomerAttributes
     */
    private $getAllowedCustomerAttributes;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param ChangeSubscriptionStatus $changeSubscriptionStatus
     * @param GetAllowedCustomerAttributes $getAllowedCustomerAttributes
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        ChangeSubscriptionStatus $changeSubscriptionStatus,
        GetAllowedCustomerAttributes $getAllowedCustomerAttributes
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->storeManager = $storeManager;
        $this->changeSubscriptionStatus = $changeSubscriptionStatus;
        $this->getAllowedCustomerAttributes = $getAllowedCustomerAttributes;
    }

    /**
     * Creates new customer account
     *
     * @param array $data
     * @return CustomerInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerInterface
    {
        try {
            $customer = $this->createAccount($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        if (isset($data['is_subscribed'])) {
            $this->changeSubscriptionStatus->execute((int)$customer->getId(), (bool)$data['is_subscribed']);
        }
        return $customer;
    }

    /**
     * Create account
     *
     * @param array $data
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function createAccount(array $data): CustomerInterface
    {
        $this->validateData($data);
        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $data,
            CustomerInterface::class
        );
        $store = $this->storeManager->getStore();
        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($store->getId());

        $password = array_key_exists('password', $data) ? $data['password'] : null;
        return $this->accountManagement->createAccount($customerDataObject, $password);
    }

    /**
     * @param array $customerData
     * @return void
     * @throws GraphQlInputException
     */
    public function validateData(array $customerData): void
    {
        $attributes = $this->getAllowedCustomerAttributes->execute();
        $errorInput = [];

        foreach ($attributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (isset($customerData[$attributeName]) && empty($customerData[$attributeName]))
            ) {
                $errorInput[] = $attributeName;
            }
        }

        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameters are missing: %1', [implode(', ', $errorInput)])
            );
        }
    }
}
