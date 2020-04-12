<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes\Config;

use Magento\Catalog\Model\ProductTypes\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    protected function setUp(): void
    {
        $this->_model = new Converter();
        $this->_filePath = realpath(__DIR__) . '/_files/';
    }

    public function testConvertIfNodeNotExist()
    {
        $source = $this->_filePath . 'product_types.xml';
        $dom = new \DOMDocument();
        $dom->load($source);
        $expected = include $this->_filePath . 'product_types.php';
        $this->assertEquals($expected, $this->_model->convert($dom));
    }
}
