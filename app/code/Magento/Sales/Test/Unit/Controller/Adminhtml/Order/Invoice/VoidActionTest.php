<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\VoidAction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VoidActionTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $titleMock;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var UpdateQty
     */
    protected $controller;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var MockObject
     */
    protected $invoiceManagement;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceManagement = $this->getMockBuilder(InvoiceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(InvoiceManagementInterface::class)
            ->willReturn($this->invoiceManagement);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getTitle',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory'
                ]
            )
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            VoidAction::class,
            [
                'context' => $contextMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'invoiceRepository',
            $this->invoiceRepository
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsInProcess', '__wakeup'])
            ->getMock();

        $this->invoiceManagement->expects($this->once())
            ->method('setVoid')
            ->with($invoiceId)
            ->willReturn(true);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));
        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));

        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock->expects($this->at(0))
            ->method('addObject')
            ->with($invoiceMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(1))
            ->method('addObject')
            ->with($orderMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(2))
            ->method('save');

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(Transaction::class)
            ->will($this->returnValue($transactionMock));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The invoice has been voided.');

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteNoInvoice()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultForward->expects($this->once())->method('forward')->with(('noroute'))->will($this->returnSelf());

        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultForward));

        $this->assertSame($resultForward, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteModelException()
    {
        $invoiceId = 2;
        $message = 'test message';
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $this->invoiceManagement->expects($this->once())
            ->method('setVoid')
            ->with($invoiceId)
            ->will($this->throwException($e));

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));
        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
