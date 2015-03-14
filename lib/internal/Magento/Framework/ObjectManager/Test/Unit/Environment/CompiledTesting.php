<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Environment;

use Magento\Framework\ObjectManager\Environment\Compiled;

require 'ConfigTesting.php';

class CompiledTesting extends Compiled
{
    /**
     * @return array
     */
    protected function getConfigData()
    {
        return [];
    }

    /**
     * @return \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    public function getDiConfig()
    {
        return new ConfigTesting();
    }
}
