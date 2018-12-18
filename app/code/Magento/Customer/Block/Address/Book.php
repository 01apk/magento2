<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\App\ObjectManager;
use Magento\Directory\Model\CountryFactory;

/**
 * Customer address book block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressesCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private $addressesCollection;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     * @param \Magento\Customer\Model\ResourceModel\Address\Collection $addressesCollection
     * @param CountryFactory|null $countryFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = [],
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressesCollectionFactory = null,
        CountryFactory $countryFactory = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->currentCustomer = $currentCustomer;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->addressesCollectionFactory = $addressesCollectionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Customer\Model\ResourceModel\Address\CollectionFactory::class);
        $this->countryFactory = $countryFactory ?: ObjectManager::getInstance()->get(CountryFactory::class);

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        parent::_prepareLayout();
        $addresses = $this->getAddressesCollection();
        if (null !== $addresses) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.addresses.pager'
            )->setCollection($this->getAddressesCollection());
            $this->setChild('pager', $pager);
            $this->getAddressesCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * @param int $addressId
     * @return string
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * @return bool
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->getAddressesCollection();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array($address->getId(), $primaryAddressIds)) {
                $additional[] = $address;
            }
        }
        return empty($additional) ? false : $additional;
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressHtml(\Magento\Customer\Api\Data\AddressInterface $address = null)
    {
        if ($address !== null) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return '';
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            try {
                $customer = $this->customerRepository->getById($this->currentCustomer->getCustomerId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * @return int|null
     */
    public function getDefaultBilling()
    {
        $customer = $this->getCustomer();
        if ($customer === null) {
            return null;
        } else {
            return $customer->getDefaultBilling();
        }
    }

    /**
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return int|null
     */
    public function getDefaultShipping()
    {
        $customer = $this->getCustomer();
        if ($customer === null) {
            return null;
        } else {
            return $customer->getDefaultShipping();
        }
    }

    /**
     * @param $street
     * @return string
     */
    public function getStreetAddress($street)
    {
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get customer addresses collection.
     * Filters collection by customer id
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private function getAddressesCollection()
    {
        if (null === $this->addressesCollection) {
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->addressesCollectionFactory->create();
            $collection->setOrder(
                'entity_id',
                'desc'
            );
            $collection->setCustomerFilter([$this->currentCustomer->getCustomer()->getId()]);
            $this->addressesCollection = $collection;
        }

        return $this->addressesCollection;
    }

    /**
     * Get country name by $countryId
     * Using \Magento\Directory\Model\Country to get country name by $countryId
     *
     * @param $countryId
     * @return string
     */
    public function getCountryById($countryId) :string
    {
        /** @var \Magento\Directory\Model\Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryId)->getName();
    }
}
