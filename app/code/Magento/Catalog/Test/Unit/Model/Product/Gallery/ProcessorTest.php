<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for catalog product Media Gallery attribute processor.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    protected $model;

    /**
     * @var Repository|MockObject
     */
    protected $attributeRepository;

    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /**
     * @var DataObject|MockObject
     */
    protected $dataObject;

    /**
     * @var Config|MockObject
     */
    protected $mediaConfig;

    /**
     * @var Write|MockObject
     */
    protected $mediaDirectory;

    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);

        $this->attributeRepository = $this->createPartialMock(
            Repository::class,
            ['get']
        );

        $fileStorageDb = $this->createMock(Database::class);

        $this->mediaConfig = $this->createMock(Config::class);

        $this->mediaDirectory = $this->createMock(Write::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectory);

        $resourceModel = $this->createPartialMock(
            Gallery::class,
            ['getMainTable']
        );
        $resourceModel->expects($this->any())
            ->method('getMainTable')
            ->willReturn(
                Gallery::GALLERY_TABLE
            );

        $this->dataObject = $this->createPartialMock(
            DataObject::class,
            ['getIsDuplicate', 'isLockedAttribute', 'getMediaAttributes']
        );

        $this->model = $this->objectHelper->getObject(
            Processor::class,
            [
                'attributeRepository' => $this->attributeRepository,
                'fileStorageDb' => $fileStorageDb,
                'mediaConfig' => $this->mediaConfig,
                'filesystem' => $filesystem,
                'resourceModel' => $resourceModel
            ]
        );
    }

    public function testGetAffectedFields()
    {
        $valueId = 2345;
        $attributeId = 345345;

        $attribute = $this->createPartialMock(
            Attribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup']
        );
        $attribute->expects($this->any())->method('getName')->will($this->returnValue('image'));
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));
        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));
        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $object = new DataObject();
        $object->setImage(['images' => [['value_id' => $valueId]]]);
        $object->setId(555);

        $this->assertEquals(
            [
                Gallery::GALLERY_TABLE => [
                    ['value_id' => $valueId, 'attribute_id' => 345345, 'entity_id' => $object->getId()],
                ],
            ],
            $this->model->getAffectedFields($object)
        );
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool $value
     */
    public function testValidate($value)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->createPartialMock(
            Attribute::class,
            ['getAttributeCode', 'getIsRequired', 'isValueEmpty', 'getIsUnique', 'getEntity', '__wakeup']
        );
        $attributeEntity = $this->getMockBuilder(AbstractResource::class)
            ->setMethods(['checkAttributeUniqueValue'])
            ->getMockForAbstractClass();

        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $attribute->expects($this->any())->method('getIsRequired')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('isValueEmpty')->will($this->returnValue($value));
        $attribute->expects($this->any())->method('getIsUnique')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('getEntity')->will($this->returnValue($attributeEntity));
        $attributeEntity->expects($this->any())->method('checkAttributeUniqueValue')->will($this->returnValue(true));

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $this->dataObject->setData(['attr_code' => 'attribute data']);
        $this->assertEquals(!$value, $this->model->validate($this->dataObject));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @param int $setDataExpectsCalls
     * @param string|null $setDataArgument
     * @param array|string $mediaAttribute
     * @dataProvider clearMediaAttributeDataProvider
     */
    public function testClearMediaAttribute($setDataExpectsCalls, $setDataArgument, $mediaAttribute)
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->exactly($setDataExpectsCalls))
            ->method('setData')
            ->with($setDataArgument, 'no_selection');

        $this->mediaConfig->expects($this->once())
            ->method('getMediaAttributeCodes')
            ->willReturn(['image', 'small_image']);

        $this->assertSame($this->model, $this->model->clearMediaAttribute($productMock, $mediaAttribute));
    }

    /**
     * @return array
     */
    public function clearMediaAttributeDataProvider()
    {
        return [
            [
                'setDataExpectsCalls' => 1,
                'setDataArgument' => 'image',
                'mediaAttribute' => 'image',
            ],
            [
                'setDataExpectsCalls' => 1,
                'setDataArgument' => 'image',
                'mediaAttribute' => ['image'],
            ],
            [
                'setDataExpectsCalls' => 0,
                'setDataArgument' => null,
                'mediaAttribute' => 'some_image',
            ],
            [
                'setDataExpectsCalls' => 0,
                'setDataArgument' => null,
                'mediaAttribute' => ['some_image'],
            ],
        ];
    }
}
