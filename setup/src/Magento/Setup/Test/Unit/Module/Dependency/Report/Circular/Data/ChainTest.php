<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Circular\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Circular\Data\Chain;
use PHPUnit\Framework\TestCase;

class ChainTest extends TestCase
{
    public function testGetModules()
    {
        $modules = ['foo', 'baz', 'bar'];

        $objectManagerHelper = new ObjectManager($this);
        /** @var Chain $chain */
        $chain = $objectManagerHelper->getObject(
            Chain::class,
            ['modules' => $modules]
        );

        $this->assertEquals($modules, $chain->getModules());
    }
}
