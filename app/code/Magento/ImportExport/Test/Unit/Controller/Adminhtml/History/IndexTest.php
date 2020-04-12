<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\History;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Controller\Adminhtml\History\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Index
     */
    protected $indexController;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    protected $resultPage;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->resultPage = $this->createPartialMock(
            Page::class,
            ['setActiveMenu', 'getConfig', 'getTitle', 'prepend', 'addBreadcrumb']
        );
        $this->resultPage->expects($this->any())->method('getConfig')->willReturnSelf();
        $this->resultPage->expects($this->any())->method('getTitle')->willReturnSelf();
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->resultFactory->expects($this->any())->method('create')->willReturn($this->resultPage);
        $this->context = $this->createPartialMock(Context::class, ['getResultFactory']);
        $this->context->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactory);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->indexController = $this->objectManagerHelper->getObject(
            Index::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $result = $this->indexController->execute();
        $this->assertNotNull($result);
    }
}
