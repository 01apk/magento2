<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Simplexml;

use Magento\Shipping\Model\Simplexml\Element;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    public function testXmlentities()
    {
        $xmlElement = new Element('<xml></xml>');
        $this->assertEquals('&amp;copy;&amp;', $xmlElement->xmlentities('&copy;&amp;'));
    }
}
