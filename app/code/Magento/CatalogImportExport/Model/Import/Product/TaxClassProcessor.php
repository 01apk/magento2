<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Tax\Model\ClassModel;

class TaxClassProcessor
{
    const ATRR_CODE = 'tax_class_id';

    /**
     * tax classes
     */
    protected $taxClasses;

    /**
     * @var \Magento\Tax\Model\Resource\TaxClass\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $classModelFactory;

    /**
     * @param \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $collectionFactory
     * @param \Magento\Tax\Model\ClassModelFactory $classModelFactory
     */
    public function __construct(
        \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $collectionFactory,
        \Magento\Tax\Model\ClassModelFactory $classModelFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->classModelFactory = $classModelFactory;
        $this->initTaxClasses();
    }

    /**
     * @return $this
     */
    protected function initTaxClasses()
    {
        if (empty($this->taxClasses)) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('class_type', ClassModel::TAX_CLASS_TYPE_PRODUCT);
            /* @var $collection \Magento\Tax\Model\Resource\TaxClass\Collection */
            foreach ($collection as $taxClass) {
                $this->taxClasses[$taxClass->getClassName()] = $taxClass->getId();
            }
        }
        return $this;
    }

    /**
     * Creates new tax class
     *
     * @param $taxClassName
     * @param AbstractType $productTypeModel
     * @return mixed
     */
    protected function createTaxClass($taxClassName, AbstractType  $productTypeModel)
    {
        /** @var \Magento\Tax\Model\ClassModelFactory $taxClass */
        $taxClass = $this->classModelFactory->create();
        $taxClass->setClassType(ClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass->setClassName($taxClassName);
        $taxClass->save();

        $id = $taxClass->getId();

        $productTypeModel->addAttributeOption(self::ATRR_CODE, $id, $id);

        return $id;
    }


    /**
     * @param $taxClassName
     * @param AbstractType $productTypeModel
     * @return mixed
     */
    public function upsertTaxClass($taxClassName, AbstractType $productTypeModel)
    {
        if (!isset($this->taxClasses[$taxClassName])) {
            $this->taxClasses[$taxClassName] = $this->createTaxClass($taxClassName, $productTypeModel);
        }

        return $this->taxClasses[$taxClassName];
    }
}
