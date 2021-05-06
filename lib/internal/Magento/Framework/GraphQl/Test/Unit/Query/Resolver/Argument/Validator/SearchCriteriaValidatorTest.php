<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query\Resolver\Argument\Validator;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\SearchCriteriaValidator;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of graphql search criteria validator
 */
class SearchCriteriaValidatorTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testValidValue()
    {
        $validator = new SearchCriteriaValidator(1, 3);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator->validate($field, ['pageSize' => 1]);
        $validator->validate($field, ['pageSize' => 2]);
        $validator->validate($field, ['pageSize' => 3]);
    }

    public function testValidInvalidMinValue()
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Minimum pageSize is 1");
        $validator = new SearchCriteriaValidator(1, 3);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator->validate($field, ['pageSize' => 0]);
    }

    public function testValidInvalidMaxValue()
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Maximum pageSize is 3");
        $validator = new SearchCriteriaValidator(1, 3);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator->validate($field, ['pageSize' => 4]);
    }
}
