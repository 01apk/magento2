<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\AuthenticationDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\Request\RequestTypeBuilder;

class RequestTypeBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AuthenticationDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new RequestTypeBuilder('foo');
    }

    public function testBuild()
    {
        $expected = [
            'payload_type' => 'foo'
        ];

        $buildSubject = [];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
