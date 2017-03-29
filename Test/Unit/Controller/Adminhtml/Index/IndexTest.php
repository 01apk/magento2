<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\AsynchronousOperations\Controller\Adminhtml\Index\Index
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    protected function setUp()
    {
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->viewMock = $this->getMock(\Magento\Framework\App\ViewInterface::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultFactoryMock = $this->getMock(
            \Magento\Framework\View\Result\PageFactory::class,
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            \Magento\AsynchronousOperations\Controller\Adminhtml\Index\Index::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock,
                'resultPageFactory' => $this->resultFactoryMock

            ]
        );
    }

    public function testExecute()
    {
        $itemId = 'Magento_Logging::system_magento_logging_events';
        $prependText = 'Bulk Actions Log';
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class, [], [], '', false);
        $menuModelMock = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);
        $pageMock = $this->getMock(\Magento\Framework\View\Result\Page::class, [], [], '', false);
        $pageConfigMock = $this->getMock(\Magento\Framework\View\Page\Config::class, [], [], '', false);
        $titleMock = $this->getMock(\Magento\Framework\View\Page\Title::class, [], [], '', false);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);

        $blockMock = $this->getMock(
            \Magento\Framework\View\Element\BlockInterface::class,
            ['setActive', 'getMenuModel', 'toHtml'],
            [],
            '',
            false
        );

        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setActive')->with($itemId);
        $blockMock->expects($this->once())->method('getMenuModel')->willReturn($menuModelMock);
        $menuModelMock->expects($this->once())->method('getParentItems')->willReturn([]);

        $pageMock->expects($this->once())->method('getConfig')->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())->method('getTitle')->willReturn($titleMock);
        $titleMock->expects($this->once())->method('prepend')->with($prependText);
        $pageMock->expects($this->once())->method('initLayout');
        $this->model->execute();
    }
}
