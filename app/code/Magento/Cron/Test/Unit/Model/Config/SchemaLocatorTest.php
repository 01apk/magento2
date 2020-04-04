<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config;

use Magento\Cron\Model\Config\SchemaLocator;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject|ModuleDirReader
     */
    private $moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->moduleReaderMock = $this->getMockBuilder(ModuleDirReader::class)
            ->disableOriginalConstructor()->getMock();
        $this->moduleReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'Magento_Cron')
            ->will($this->returnValue('schema_dir'));
        $this->locator = new SchemaLocator($this->moduleReaderMock);
    }

    /**
     * Testing that schema has file
     */
    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/crontab.xsd', $this->locator->getSchema());
    }
}
