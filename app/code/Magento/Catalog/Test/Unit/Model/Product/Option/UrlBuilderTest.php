<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product\Option\UrlBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    /**
     * @var UrlBuilder
     */
    private $model;

    public function testGetUrl()
    {
        $this->assertEquals('testResult', $this->model->getUrl('router', []));
    }

    protected function setUp(): void
    {
        $mockedFrontendUrlBuilder = $this->getMockedFrontendUrlBuilder();
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            UrlBuilder::class,
            ['frontendUrlBuilder' => $mockedFrontendUrlBuilder]
        );
    }

    /**
     * @return UrlInterface
     */
    private function getMockedFrontendUrlBuilder()
    {
        $mockBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('testResult'));

        return $mock;
    }
}
