<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\VaultDataBuilder;
use PHPUnit\Framework\TestCase;

class VaultDataBuilderTest extends TestCase
{
    public function testBuild()
    {
        $expectedResult = [
            VaultDataBuilder::OPTIONS => [
                VaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true
            ]
        ];

        $buildSubject = [];

        $builder = new VaultDataBuilder();
        static::assertEquals(
            $expectedResult,
            $builder->build($buildSubject)
        );
    }
}
