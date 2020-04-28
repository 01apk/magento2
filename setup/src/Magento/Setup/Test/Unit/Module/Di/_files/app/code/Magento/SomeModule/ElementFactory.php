<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule;

use Magento\Framework\ObjectManagerInterface;

require_once __DIR__ . '/Element.php';
class ElementFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $className
     * @param array $data
     * @return mixed
     */
    public function create($className, array $data = [])
    {
        $instance = $this->_objectManager->create($className, $data);
        return $instance;
    }
}
