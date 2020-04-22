<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter\Text;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /** @var Text|MockObject */
    private $textFilterBlock;

    protected function setUp(): void
    {
        $this->textFilterBlock = (new ObjectManager($this))->getObject(
            Text::class
        );
    }

    public function testGetCondition()
    {
        $value = "test";
        $this->textFilterBlock->setValue($value);
        $this->assertSame(["like" => $value], $this->textFilterBlock->getCondition());
    }
}
