<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Config;

use Magento\Backend\Model\Menu\Config\SchemaLocator;
use Magento\Framework\Module\Dir\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->createMock(Reader::class);
        $this->_moduleReaderMock->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Backend'
        )->will(
            $this->returnValue('schema_dir')
        );
        $this->_model = new SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/menu.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(null, $this->_model->getPerFileSchema());
    }
}
