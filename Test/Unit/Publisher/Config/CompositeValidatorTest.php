<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config;

use Magento\Framework\MessageQueue\Publisher\Config\CompositeValidator;
use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;

class CompositeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeValidator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorOneMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorTwoMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->validatorOneMock = $this->getMock(ValidatorInterface::class);
        $this->validatorTwoMock = $this->getMock(ValidatorInterface::class);

        $this->model = new CompositeValidator([$this->validatorOneMock, $this->validatorTwoMock]);
    }

    public function testValidate()
    {
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $this->validatorOneMock->expects($this->once())->method('validate')->with($expectedValidationData);
        $this->validatorTwoMock->expects($this->once())->method('validate')->with($expectedValidationData);
        $this->model->validate($expectedValidationData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage test
     */
    public function testValidatorThrowsException()
    {
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $this->validatorOneMock
            ->expects($this->once())
            ->method('validate')
            ->willThrowException(new \LogicException('test'));
        $this->validatorTwoMock->expects($this->never())->method('validate');
        $this->model->validate($expectedValidationData);
    }

    public function testInvalidReaderInstance()
    {
        $this->setExpectedException(
            '\LogicException',
            'Validator does not implements Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface'
        );
        $validator = $this->getMock(\Magento\Framework\Config\ReaderInterface::class);
        $model = new CompositeValidator([$validator]);
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $model->validate($expectedValidationData);
    }
}
