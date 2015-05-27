<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Field;

class Resolver implements ResolverInterface
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($fields)
    {
        $resolvedFields = [];
        foreach ((array)$fields as $field) {
            $resolvedFields[] = $this->fieldFactory->create(['column' => $field]);
        }

        return $resolvedFields;
    }
}
