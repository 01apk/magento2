<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column\Multistore;
use Magento\Backend\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultistoreTest extends TestCase
{
    /**
     * @var Multistore
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(StoreManager::class);

        $arguments = [
            'storeManager' => $this->_storeManagerMock,
            'urlBuilder' => $this->createMock(Url::class),
        ];

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            Multistore::class,
            $arguments
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_storeManagerMock);
    }

    public function testIsDisplayedReturnsTrueInMultiStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(false));
        $this->assertTrue($this->_model->isDisplayed());
    }

    public function testIsDisplayedReturnsFalseInSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertFalse($this->_model->isDisplayed());
    }
}
