<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Webapi\Controller\ServiceArgsSerializer;
use Magento\Webapi\Service\Entity\TestService;

class DataFromArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceArgsSerializer */
    protected $serializer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $attributeValueBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $serviceConfigReader;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectFactory = new \Magento\Webapi\Service\Entity\WebapiBuilderFactory($objectManager);
        /** @var \Magento\Framework\Reflection\TypeProcessor $typeProcessor */
        $typeProcessor = $objectManager->getObject('Magento\Framework\Reflection\TypeProcessor');
        $cache = $this->getMockBuilder('Magento\Webapi\Model\Cache\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->expects($this->any())->method('load')->willReturn(false);

        $this->serviceConfigReader = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeValueBuilder = $this->getMockBuilder('Magento\Framework\Api\AttributeDataBuilder')
            ->setMethods(['setAttributeCode', 'setValue', 'create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $objectManager->getObject(
            'Magento\Webapi\Controller\ServiceArgsSerializer',
            [
                'typeProcessor' => $typeProcessor,
                'builderFactory' => $objectFactory,
                'cache' => $cache,
                'serviceConfigReader' => $this->serviceConfigReader,
                'attributeValueBuilder' => $this->attributeValueBuilder
            ]
        );
    }

    public function testSimpleProperties()
    {
        $data = ['entityId' => 15, 'name' => 'Test'];
        $result = $this->serializer->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'simple', $data);
        $this->assertNotNull($result);
        $this->assertEquals(15, $result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testNonExistentPropertiesWithDefaultArgumentValue()
    {
        $data = [];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'simpleDefaultValue',
            $data
        );
        $this->assertNotNull($result);
        $this->assertEquals(\Magento\Webapi\Service\Entity\TestService::DEFAULT_VALUE, $result[0]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage \Magento\Framework\Exception\InputException::DEFAULT_MESSAGE
     */
    public function testNonExistentPropertiesWithoutDefaultArgumentValue()
    {
        $data = [];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'simple',
            $data
        );
        $this->assertNull($result);
    }

    public function testNestedDataProperties()
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedData',
            $data
        );
        $this->assertNotNull($result);
        $this->assertTrue($result[0] instanceof Nested);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        $this->assertNotEmpty($result[0]);
        /** @var NestedData $arg */
        $arg = $result[0];
        $this->assertTrue($arg instanceof Nested);
        /** @var SimpleData $details */
        $details = $arg->getDetails();
        $this->assertNotNull($details);
        $this->assertTrue($details instanceof Simple);
        $this->assertEquals(15, $details->getEntityId());
        $this->assertEquals('Test', $details->getName());
    }

    public function testSimpleArrayProperties()
    {
        $data = ['ids' => [1, 2, 3, 4]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'simpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $ids */
        $ids = $result[0];
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['ids'], $ids);
    }

    public function testAssociativeArrayProperties()
    {
        $data = ['associativeArray' => ['key' => 'value', 'key_two' => 'value_two']];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value_two', $associativeArray['key_two']);
    }

    public function testAssociativeArrayPropertiesWithItem()
    {
        $data = ['associativeArray' => ['item' => 'value']];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray[0]);
    }

    public function testAssociativeArrayPropertiesWithItemArray()
    {
        $data = ['associativeArray' => ['item' => ['value1','value2']]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $associativeArray */
        $array = $result[0];
        $this->assertNotNull($array);
        $this->assertEquals('value1', $array[0]);
        $this->assertEquals('value2', $array[1]);
    }

    public function testArrayOfDataObjectProperties()
    {
        $data = [
            'dataObjects' => [
                ['entityId' => 14, 'name' => 'First'],
                ['entityId' => 15, 'name' => 'Second'],
            ],
        ];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'dataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $dataObjects */
        $dataObjects = $result[0];
        $this->assertEquals(2, count($dataObjects));
        /** @var SimpleData $first */
        $first = $dataObjects[0];
        /** @var SimpleData $second */
        $second = $dataObjects[1];
        $this->assertTrue($first instanceof Simple);
        $this->assertEquals(14, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof Simple);
        $this->assertEquals(15, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    public function testNestedSimpleArrayProperties()
    {
        $data = ['arrayData' => ['ids' => [1, 2, 3, 4]]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedSimpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var SimpleArrayData $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof SimpleArray);
        /** @var array $ids */
        $ids = $dataObject->getIds();
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['arrayData']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties()
    {
        $data = [
            'associativeArrayData' => ['associativeArray' => ['key' => 'value', 'key2' => 'value2']],
        ];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var AssociativeArray $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof AssociativeArray);
        /** @var array $associativeArray */
        $associativeArray = $dataObject->getAssociativeArray();
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value2', $associativeArray['key2']);
    }

    public function testNestedArrayOfDataObjectProperties()
    {
        $data = [
            'dataObjects' => [
                'items' => [['entityId' => 1, 'name' => 'First'], ['entityId' => 2, 'name' => 'Second']],
            ],
        ];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedDataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var DataArrayData $dataObjects */
        $dataObjects = $result[0];
        $this->assertTrue($dataObjects instanceof DataArray);
        /** @var array $items */
        $items = $dataObjects->getItems();
        $this->assertEquals(2, count($items));
        /** @var SimpleData $first */
        $first = $items[0];
        /** @var SimpleData $second */
        $second = $items[1];
        $this->assertTrue($first instanceof Simple);
        $this->assertEquals(1, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof Simple);
        $this->assertEquals(2, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    /**
     * Covers object with custom attributes
     *
     * @dataProvider customAttributesDataProvider
     */
    public function testCustomAttributesProperties($customAttributeType, $customAttributeValue)
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->serviceConfigReader->expects($this->any())->method('read')->willReturn(
            [
                'Magento\Webapi\Service\Entity\ObjectWithCustomAttributes' => [
                    TestService::CUSTOM_ATTRIBUTE_CODE => $customAttributeType
                ]
            ]
        );

        $dataObject = $objectManager->getObject(
            'Magento\Framework\Api\AttributeValue',
            ['data' => ['attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE, 'value' => $customAttributeValue]]
        );

        $this->attributeValueBuilder->expects($this->any())->method('create')->willReturn($dataObject);
        $this->attributeValueBuilder->expects($this->any())->method('setValue')->willReturn(
            $this->attributeValueBuilder
        );
        $this->attributeValueBuilder->expects($this->any())->method('setAttributeCode')->willReturn(
            $this->attributeValueBuilder
        );

        $data = [
            'param' => [
                'customAttributes' => [
                    ['attribute_code' => TestService::CUSTOM_ATTRIBUTE_CODE, 'value' => $customAttributeValue]
                ]
            ]
        ];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'ObjectWithCustomAttributesMethod',
            $data
        );

        /** @var \Magento\Framework\Api\AttributeValue $obj */
        $obj = $result[0];

        $this->assertEquals(
            $customAttributeValue,
            $obj->getCustomAttribute(TestService::CUSTOM_ATTRIBUTE_CODE)->getValue()
        );
        $this->assertEquals(
            TestService::CUSTOM_ATTRIBUTE_CODE,
            $obj->getCustomAttribute(TestService::CUSTOM_ATTRIBUTE_CODE)->getAttributeCode());
    }

    /**
     * Provides data for testCustomAttributesProperties
     *
     * @return array
     */
    public function customAttributesDataProvider()
    {
        return [
            'customAttributeInteger' => [
                'type' => 'integer[]',
                'value' => [TestService::DEFAULT_VALUE]
            ],
            'customAttributeObject' => [
                'type' => '\Magento\Webapi\Service\Entity\SimpleArray',
                'value' => ['ids' => [1, 2, 3, 4]]
            ],
        ];
    }
}
