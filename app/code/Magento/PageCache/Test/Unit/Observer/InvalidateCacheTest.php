<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\Observer;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Observer\InvalidateCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvalidateCacheTest extends TestCase
{
    /** @var InvalidateCache */
    protected $_model;

    /** @var MockObject|Config */
    protected $_configMock;

    /** @var  MockObject|TypeListInterface */
    protected $_typeListMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject|
     */
    protected $observerMock;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->_configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->_typeListMock = $this->createMock(TypeList::class);

        $this->observerMock = $this->createMock(Observer::class);

        $this->_model = new InvalidateCache(
            $this->_configMock,
            $this->_typeListMock
        );
    }

    /**
     * @dataProvider invalidateCacheDataProvider
     * @param bool $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->once())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($cacheState) {
            $this->_typeListMock->expects($this->once())->method('invalidate')->with($this->equalTo('full_page'));
        }

        $this->_model->execute($this->observerMock);
    }

    /**
     * @return array
     */
    public function invalidateCacheDataProvider()
    {
        return [[true], [false]];
    }
}
