<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Listing\Column;

use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\AsynchronousOperations\Ui\Component\Listing\Column\Actions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactory;

    /**
     * @var Actions
     */
    private $actionColumn;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $processor = $this->createPartialMock(
            Processor::class,
            ['getProcessor']
        );
        $this->context->expects($this->never())->method('getProcessor')->will($this->returnValue($processor));
        $objectManager = new ObjectManager($this);
        $this->actionColumn = $objectManager->getObject(
            Actions::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'components' => [],
                'data' => ['name' => 'Edit'],
                'editUrl' => ''
            ]
        );
    }

    /**
     * Test for method prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $href = 'bulk/bulk/details/id/bulk-1';
        $this->context->expects($this->once())->method('getUrl')->with(
            'bulk/bulk/details',
            ['uuid' => 'bulk-1']
        )->willReturn($href);
        $dataSource['data']['items']['item'] = [BulkSummary::BULK_ID => 'bulk-1'];
        $actionColumn['data']['items']['item'] = [
            'Edit' => [
                'edit' => [
                    'href' => $href,
                    'label' => __('Details'),
                    'hidden' => false
                ]
            ]
        ];
        $expectedResult = array_merge_recursive($dataSource, $actionColumn);
        $this->assertEquals($expectedResult, $this->actionColumn->prepareDataSource($dataSource));
    }
}
