<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition\Sql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rule\Model\Condition\Sql\Expression;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public function testExpression()
    {
        $expression = (new ObjectManagerHelper($this))->getObject(
            Expression::class,
            ['expression' => 'SQL_EXPRESSION']
        );
        $this->assertEquals('(SQL_EXPRESSION)', (string)$expression);
    }
}
