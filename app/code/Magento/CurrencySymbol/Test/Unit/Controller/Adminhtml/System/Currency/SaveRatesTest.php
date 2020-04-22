<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currency;

use Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveRatesTest extends TestCase
{
    /**
     * @var SaveRates
     */
    protected $action;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->responseMock = $this->createPartialMock(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->action = $objectManager->getObject(
            SaveRates::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
            ]
        );
    }

    public function testWithNullRateExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rate')
            ->willReturn(null);

        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->action->execute();
    }
}
