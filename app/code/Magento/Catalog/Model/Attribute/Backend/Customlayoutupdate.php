<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Layout update attribute backend
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Customlayoutupdate extends AbstractBackend
{
    /**
     * @var ValidatorFactory
     * @deprecated Is not used anymore.
     */
    protected $_layoutUpdateValidatorFactory;

    /**
     * @param ValidatorFactory $layoutUpdateValidatorFactory
     */
    public function __construct(ValidatorFactory $layoutUpdateValidatorFactory)
    {
        $this->_layoutUpdateValidatorFactory = $layoutUpdateValidatorFactory;
    }

    /**
     * Extract an attribute value.
     *
     * @param AbstractModel $object
     * @return mixed
     */
    private function extractValue(AbstractModel $object)
    {
        $attributeCode = $attributeCode ?? $this->getAttribute()->getName();
        $value = $object->getData($attributeCode);
        if (!$value) {
            $value = null;
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @param AbstractModel $object
     */
    public function validate($object)
    {
        if (parent::validate($object)) {
            $attrCode = $this->getAttribute()->getAttributeCode();
            if ($object instanceof AbstractModel) {
                $value = $this->extractValue($object);
                if ($value && $object->getOrigData($attrCode) !== $value) {
                    throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
                }
            }
        }

        return true;
    }

    /**
     * Put an attribute value.
     *
     * @param AbstractModel $object
     * @param string|null $value
     * @return void
     */
    private function putValue(AbstractModel $object, ?string $value): void
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($object->hasData(AbstractModel::CUSTOM_ATTRIBUTES)) {
            $object->setCustomAttribute($attributeCode, $value);
        }
        $object->setData($attributeCode, $value);
    }

    /**
     * @inheritDoc
     *
     * @param AbstractModel $object
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        //Validate first, validation might have been skipped.
        $this->validate($object);
        $this->putValue($object, $this->extractValue($object));

        return parent::beforeSave($object);
    }
}
