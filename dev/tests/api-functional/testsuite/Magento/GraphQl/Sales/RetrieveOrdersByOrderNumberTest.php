<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class RetrieveOrdersTest
 */
class RetrieveOrdersByOrderNumberTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var Order\Item */
    private $orderItem;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);

        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->orderItem = $objectManager->get(Order\Item::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerOrdersSimpleProductQuery()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000003"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
      order_items{
        quantity_ordered
        product_sku
        product_url
        product_name
        product_sale_price{currency value}
      }
    }
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'][0];
        self::assertCount(1, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('order_items', $customerOrderItemsInResponse);
        $this->assertNotEmpty($customerOrderItemsInResponse['order_items']);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', '100000003')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $items = $this->orderRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $orderId = $item->getEntityId();
            $orderNumber = $item->getIncrementId();
            $this->assertEquals($orderId, $customerOrderItemsInResponse['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse['status']);
        }
        $expectedOrderItems =
            [ 'quantity_ordered'=> 2,
                'product_sku'=> 'simple',
                "product_url"=> 'url',
                'product_name'=> 'Simple Product',
                'product_sale_price'=> ['currency'=> null, 'value'=> 10]
            ];
        $actualOrderItemsFromResponse = $customerOrderItemsInResponse['order_items'][0];
        $this->assertEquals($expectedOrderItems, $actualOrderItemsFromResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetMultipleCustomerOrdersQuery()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{in:["100000002","100000003"]}}){
    total_count
    items
    {
      id
      number
      status
      order_date
      order_items{
        quantity_ordered
        product_sku
        product_url
        product_name
        parent_product_sku
        product_sale_price{currency value}
      }
    }
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(2, $response['customer']['orders']['total_count']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'];
        $this->assertCount(2, $response['customer']['orders']['items']);

        $orderNumbers = ['100000002', '100000003'];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderNumbers, 'in')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $items = $this->orderRepository->getList($searchCriteria)->getItems();
        $key = 0;
        foreach ($items as $item) {
            $orderId = $item->getEntityId();
            $orderNumber = $item->getIncrementId();
            //$orderStatus = $item->getStatus();//getStatusFrontendLabel($this->getStatus()
            $this->assertEquals($orderId, $customerOrderItemsInResponse[$key]['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse[$key]['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse[$key]['status']);
            $key++;
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testGetCustomerOrdersUnauthorizedCustomer()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000001"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
    }
   }
 }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/two_orders_for_two_diff_customers.php
     */
    public function testGetCustomerOrdersWithWrongCustomer()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000001"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
    }
   }
 }
}
QUERY;
        $currentEmail = 'customer_two@example.com';
        $currentPassword = 'password';
        $responseWithWrongCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['total_count']);
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['items']);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $responseWithCorrectCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['total_count']);
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/order_with_totals.php
     */
    public function testGetCustomerOrdersOnTotals()
    {
        $query =
            <<<QUERY
{
  customer {
    email
    orders(filter:{number:{eq:"100000001"}}) {
      total_count
      items {
        id
        number
        order_date
        status
        totals {
          base_grand_total {
            value
            currency
          }
          grand_total {
            value
            currency
          }
          shipping_handling {
            value
            currency
          }
          subtotal {
            value
            currency
          }
          tax {
            value
            currency
          }
          discounts {
            amount {
              value
              currency
            }
            label
          }
        }
      }
    }
  }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);

        $this->assertEquals(
            100,
            $response['customer']['orders']['items'][0]['totals']['base_grand_total']['value']
        );
        $this->assertEquals(
            'USD',
            $response['customer']['orders']['items'][0]['totals']['base_grand_total']['currency']
        );
        $this->assertEquals(
            100,
            $response['customer']['orders']['items'][0]['totals']['grand_total']['value']
        );
        $this->assertEquals(
            'USD',
            $response['customer']['orders']['items'][0]['totals']['grand_total']['currency']
        );
        $this->assertEquals(
            110,
            $response['customer']['orders']['items'][0]['totals']['subtotal']['value']
        );
        $this->assertEquals(
            'USD',
            $response['customer']['orders']['items'][0]['totals']['subtotal']['currency']
        );
        $this->assertEquals(
            10,
            $response['customer']['orders']['items'][0]['totals']['shipping_handling']['value']
        );
        $this->assertEquals(
            'USD',
            $response['customer']['orders']['items'][0]['totals']['shipping_handling']['currency']
        );
        $this->assertEquals(
            5,
            $response['customer']['orders']['items'][0]['totals']['tax']['value']
        );
        $this->assertEquals(
            'USD',
            $response['customer']['orders']['items'][0]['totals']['tax']['currency']
        );
    }

    /**
     * @param String $orderNumber
     * @dataProvider dataProviderIncorrectOrder
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerNonExistingOrderQuery(string $orderNumber)
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"{$orderNumber}"}}){
    items
    {
      number
      order_items{
        product_sku
      }
    }
    page_info {
        current_page
        page_size
        total_pages
    }
    total_count
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertCount(0, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(0, $response['customer']['orders']['total_count']);
        $this->assertArrayHasKey('page_info', $response['customer']['orders']);
        $this->assertEquals(
            ['current_page' => 1, 'page_size' => 20, 'total_pages' => 0],
            $response['customer']['orders']['page_info']
        );
    }

    /**
     * @return array
     */
    public function dataProviderIncorrectOrder(): array
    {
        return [
            'correctFormatNonExistingOrder' => [
                '200000009',
            ],
            'alphaFormatNonExistingOrder' => [
                '200AA00B9',
            ],
            'longerFormatNonExistingOrder' => [
                'X0000-0033331',
            ],
        ];
    }

    /**
     * @param String $orderNumber
     * @param String $store
     * @param String $expectedCount
     * @dataProvider dataProviderMultiStores
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/two_orders_with_order_items_two_storeviews.php
     */
    public function testGetCustomerOrdersTwoStoreviewQuery(string $orderNumber, string $store, int $expectedCount)
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"{$orderNumber}"}}){
    items
    {
      number
      order_items{
        product_sku
      }
    }
    page_info {
        current_page
        page_size
        total_pages
    }
    total_count
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            array_merge($this->getCustomerAuthHeaders($currentEmail, $currentPassword), ['Store' => $store])
        );
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertCount($expectedCount, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals($expectedCount, (int)$response['customer']['orders']['total_count']);
    }

    /**
     * @return array
     */
    public function dataProviderMultiStores(): array
    {
        return [
            'firstStoreFirstOrder' => [
                '100000001', 'default', 1
            ],
            'secondStoreSecondOrder' => [
                '100000002', 'fixture_second_store', 1
            ],
            'firstStoreSecondOrder' => [
                '100000002', 'default', 0
            ],
            'secondStoreFirstOrder' => [
                '100000001', 'fixture_second_store', 0
            ],
        ];
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
