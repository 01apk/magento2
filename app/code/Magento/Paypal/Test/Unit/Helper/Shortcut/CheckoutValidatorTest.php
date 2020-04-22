<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

use Magento\Checkout\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Paypal\Helper\Shortcut\CheckoutValidator;
use Magento\Paypal\Helper\Shortcut\Validator;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutValidatorTest extends TestCase
{
    /** @var CheckoutValidator */
    protected $checkoutValidator;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var Validator|MockObject */
    protected $paypalShortcutHelperMock;

    /** @var Data|MockObject */
    protected $paymentHelperMock;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->paypalShortcutHelperMock = $this->createMock(Validator::class);
        $this->paymentHelperMock = $this->createMock(Data::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->checkoutValidator = $this->objectManagerHelper->getObject(
            CheckoutValidator::class,
            [
                'checkoutSession' => $this->sessionMock,
                'shortcutValidator' => $this->paypalShortcutHelperMock,
                'paymentData' => $this->paymentHelperMock
            ]
        );
    }

    public function testValidate()
    {
        $code = 'code';
        $isInCatalog = true;
        $methodInstanceMock = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->paypalShortcutHelperMock->expects($this->once())->method('isContextAvailable')
            ->with($code, $isInCatalog)->will($this->returnValue(true));
        $this->paypalShortcutHelperMock->expects($this->once())->method('isPriceOrSetAvailable')
            ->with($isInCatalog)->will($this->returnValue(true));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($code)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with(null)
            ->will($this->returnValue(true));

        $this->assertTrue($this->checkoutValidator->validate($code, $isInCatalog));
    }

    public function testIsMethodQuoteAvailableNoQuoteMethodNotAvailableFalse()
    {
        $quote = null;
        $isInCatalog = true;
        $paymentCode = 'code';
        $methodInstanceMock = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($paymentCode)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with($quote)
            ->will($this->returnValue(false));

        $this->assertFalse($this->checkoutValidator->isMethodQuoteAvailable($paymentCode, $isInCatalog));
    }

    /**
     * @dataProvider methodAvailabilityDataProvider
     * @param bool $availability
     */
    public function testIsMethodQuoteAvailableWithQuoteMethodNotAvailable($availability)
    {
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->setMethods([])
            ->getMock();
        $isInCatalog = false;
        $paymentCode = 'code';
        $methodInstanceMock = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($paymentCode)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with($quote)
            ->will($this->returnValue($availability));

        $this->assertEquals(
            $availability,
            $this->checkoutValidator->isMethodQuoteAvailable($paymentCode, $isInCatalog)
        );
    }

    /**
     * @return array
     */
    public function methodAvailabilityDataProvider()
    {
        return [[true], [false]];
    }

    public function testIsQuoteSummaryValidNoQuote()
    {
        $isInCatalog = true;
        $this->assertTrue($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidMinimumAmountFalse()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->setMethods([])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(false));

        $this->assertFalse($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidGrandTotalFalse()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->setMethods(['getGrandTotal', 'validateMinimumAmount', '__wakeup'])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(true));
        $quote->expects($this->once())->method('getGrandTotal')->will($this->returnValue(0));

        $this->assertFalse($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidTrue()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->setMethods(['getGrandTotal', 'validateMinimumAmount', '__wakeup'])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(true));
        $quote->expects($this->once())->method('getGrandTotal')->will($this->returnValue(1));

        $this->assertTrue($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }
}
