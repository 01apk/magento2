<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Validator\SearchCriteriaValidator;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the search criteria validator
 */
class SearchCriteriaValidatorTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsPageSizeWhenAboveMinLimitAndBelowMaxLimit()
    {
        $searchCriteria = new SearchCriteria();
        $validator = new SearchCriteriaValidator(1, 3);
        $validator->validateEntityValue($searchCriteria, 'pageSize', 2);
    }

    public function testFailsPageSizeWhenBelowMinLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Minimum SearchCriteria pageSize is 1');
        $searchCriteria = new SearchCriteria();
        $validator = new SearchCriteriaValidator(1, 3);
        $validator->validateEntityValue($searchCriteria, 'pageSize', 0);
    }

    public function testFailsPageSizeWhenAboveMaxLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum SearchCriteria pageSize is 3');
        $searchCriteria = new SearchCriteria();
        $validator = new SearchCriteriaValidator(1, 3);
        $validator->validateEntityValue($searchCriteria, 'pageSize', 4);
    }
}
