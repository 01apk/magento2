<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Helper;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Config;
use Magento\Weee\Model\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    const ROW_AMOUNT_INVOICED = '200';
    const BASE_ROW_AMOUNT_INVOICED = '400';
    const TAX_AMOUNT_INVOICED = '20';
    const BASE_TAX_AMOUNT_INVOICED = '40';
    const ROW_AMOUNT_REFUNDED = '100';
    const BASE_ROW_AMOUNT_REFUNDED = '201';
    const TAX_AMOUNT_REFUNDED = '10';
    const BASE_TAX_AMOUNT_REFUNDED = '21';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $helperData;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $weeeConfig = $this->createMock(Config::class);
        $weeeConfig->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $weeeConfig->expects($this->any())->method('getListPriceDisplayType')->will($this->returnValue(1));
        $this->weeeTax = $this->createMock(Tax::class);
        $this->weeeTax->expects($this->any())->method('getWeeeAmount')->will($this->returnValue('11.26'));
        $this->taxData = $this->createPartialMock(
            \Magento\Tax\Helper\Data::class,
            ['getPriceDisplayType', 'priceIncludesTax']
        );

        $this->serializerMock = $this->getMockBuilder(Json::class)->getMock();

        $arguments = [
            'weeeConfig' => $weeeConfig,
            'weeeTax' => $this->weeeTax,
            'taxData' => $this->taxData,
            'serializer'  => $this->serializerMock
        ];
        $helper = new ObjectManager($this);
        $this->helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);
    }

    public function testGetAmount()
    {
        $this->product->expects($this->any())->method('hasData')->will($this->returnValue(false));
        $this->product->expects($this->any())->method('getData')->will($this->returnValue(11.26));

        $this->assertEquals('11.26', $this->helperData->getAmountExclTax($this->product));
    }

    /**
     * @return Item|MockObject
     */
    private function setupOrderItem()
    {
        $orderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $weeeTaxApplied = [
            [
                WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
            ],
            [
                WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
            ],
        ];

        $orderItem->setData(
            'weee_tax_applied',
            json_encode($weeeTaxApplied)
        );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will($this->returnValue($weeeTaxApplied));

        return $orderItem;
    }

    public function testGetWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetBaseWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeBaseTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_REFUNDED, $value);
    }

    /**
     * @dataProvider dataProviderGetWeeeAttributesForBundle
     * @param int $priceIncludesTax
     * @param bool $priceDisplay
     * @param array $expectedAmount
     */
    public function testGetWeeeAttributesForBundle($priceDisplay, $priceIncludesTax, $expectedAmount)
    {
        $prodId1 = 1;
        $prodId2 = 2;
        $fptCode1 = 'fpt' . $prodId1;
        $fptCode2 = 'fpt' . $prodId2;

        $weeeObject1 = new DataObject(
            [
                'code' => $fptCode1,
                'amount' => '15',
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $weeeObject2 = new DataObject(
            [
                'code' => $fptCode2,
                'amount' => '10',
                'amount_excl_tax' => '10.0000',
                'tax_amount' => '5'
            ]
        );
        $expectedObject1 = new DataObject(
            [
                'code' => $fptCode1,
                'amount' => $expectedAmount[0],
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $expectedObject2 = new DataObject(
            [
                'code' => $fptCode2,
                'amount' => $expectedAmount[1],
                'amount_excl_tax' => '10.0000',
                'tax_amount' => '5'
            ]
        );

        $expectedArray = [$prodId1 => [$fptCode1 => $expectedObject1], $prodId2 => [$fptCode2 => $expectedObject2]];
        $this->weeeTax->expects($this->any())
            ->method('getProductWeeeAttributes')
            ->will($this->returnValue([$weeeObject1, $weeeObject2]));
        $this->taxData->expects($this->any())
            ->method('getPriceDisplayType')
            ->willReturn($priceDisplay);
        $this->taxData->expects($this->any())
            ->method('priceIncludesTax')
            ->willReturn($priceIncludesTax);

        $productSimple = $this->createPartialMock(Simple::class, ['getId']);
        $productSimple->expects($this->at(0))
            ->method('getId')
            ->will($this->returnValue($prodId1));
        $productSimple->expects($this->at(1))
            ->method('getId')
            ->will($this->returnValue($prodId2));

        $productInstance = $this->createMock(Type::class);
        $productInstance->expects($this->any())
            ->method('getSelectionsCollection')
            ->will($this->returnValue([$productSimple]));

        $store=$this->createMock(Store::class);
        /** @var Product $product */
        $product = $this->createPartialMock(
            Product::class,
            ['getTypeInstance', 'getStoreId', 'getStore', 'getTypeId']
        );
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));
        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));
        $product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('bundle'));

        $registry=$this->createMock(Registry::class);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $result =  $this->helperData->getWeeeAttributesForBundle($product);
        $this->assertEquals($expectedArray, $result);
    }

    /**
     * @return array
     */
    public function dataProviderGetWeeeAttributesForBundle()
    {
        return [
            [2, false, ["16.00", "15.00"]],
            [2, true, ["15.00", "10.00"]],
            [1, false, ["15.00", "10.00"]],
            [1, true, ["15.00", "10.00"]],
            [3, false, ["16.00", "15.00"]],
            [3, true, ["15.00", "10.00"]],
        ];
    }

    public function testGetAppliedSimple()
    {
        $testArray = ['key' => 'value'];
        $itemProductSimple = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxApplied', 'getHasChildren']
        );
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(json_encode($testArray)));

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will($this->returnValue($testArray));

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductSimple));
    }

    public function testGetAppliedBundle()
    {
        $testArray1 = ['key1' => 'value1'];
        $testArray2 = ['key2' => 'value2'];

        $testArray = array_merge($testArray1, $testArray2);

        $itemProductSimple1=$this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getWeeeTaxApplied']);
        $itemProductSimple2=$this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getWeeeTaxApplied']);

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(json_encode($testArray1)));

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(json_encode($testArray2)));

        $itemProductBundle = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'isChildrenCalculated', 'getChildren']
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$itemProductSimple1, $itemProductSimple2]));

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will($this->returnValue($testArray));

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductBundle));
    }

    public function testGetRecursiveAmountSimple()
    {
        $testAmountUnit = 2;
        $testAmountRow = 34;

        $itemProductSimple = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($testAmountUnit));
        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($testAmountRow));

        $this->assertEquals($testAmountUnit, $this->helperData->getWeeeTaxAppliedAmount($itemProductSimple));
        $this->assertEquals($testAmountRow, $this->helperData->getWeeeTaxAppliedRowAmount($itemProductSimple));
    }

    public function testGetRecursiveAmountBundle()
    {
        $testAmountUnit1 = 1;
        $testAmountUnit2 = 2;
        $testTotalUnit = $testAmountUnit1 + $testAmountUnit2;

        $testAmountRow1 = 33;
        $testAmountRow2 = 444;
        $testTotalRow = $testAmountRow1 + $testAmountRow2;

        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($testAmountUnit1));
        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($testAmountRow1));

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($testAmountUnit2));
        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($testAmountRow2));

        $itemProductBundle = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'isChildrenCalculated', 'getChildren']
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$itemProductSimple1, $itemProductSimple2]));

        $this->assertEquals($testTotalUnit, $this->helperData->getWeeeTaxAppliedAmount($itemProductBundle));
        $this->assertEquals($testTotalRow, $this->helperData->getWeeeTaxAppliedRowAmount($itemProductBundle));
    }

    public function testGetProductWeeeAttributesForDisplay()
    {
        $store = $this->createMock(Store::class);
        $this->product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $result = $this->helperData->getProductWeeeAttributesForDisplay($this->product);
        $this->assertNull($result);
    }

    public function testGetTaxDisplayConfig()
    {
        $expected = 1;
        $taxData = $this->createPartialMock(\Magento\Tax\Helper\Data::class, ['getPriceDisplayType']);
        $taxData->expects($this->any())->method('getPriceDisplayType')->will($this->returnValue($expected));
        $arguments = [
            'taxData' => $taxData,
        ];
        $helper = new ObjectManager($this);
        $helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);

        $this->assertEquals($expected, $helperData->getTaxDisplayConfig());
    }

    public function testGetTotalAmounts()
    {
        $item1Weee = 5;
        $item2Weee = 7;
        $expected = $item1Weee + $item2Weee;
        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedRowAmount']
        );
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item1Weee);
        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item2Weee);

        $this->assertEquals($expected, $this->helperData->getTotalAmounts($items));
    }

    public function testGetBaseTotalAmounts()
    {
        $item1BaseWeee = 4;
        $item2BaseWeee = 3;
        $expected = $item1BaseWeee + $item2BaseWeee;
        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getBaseWeeeTaxAppliedRowAmnt']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getBaseWeeeTaxAppliedRowAmnt']
        );
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item1BaseWeee);
        $itemProductSimple2->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item2BaseWeee);

        $this->assertEquals($expected, $this->helperData->getBaseTotalAmounts($items));
    }
}
