<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\QuoteIdMask;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Quote\Model\QuoteIdMask
 */
class QuoteIdMaskTest extends TestCase
{
    /**
     * @var QuoteIdMask
     */
    protected $quoteIdMask;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->quoteIdMask = $helper->getObject(
            QuoteIdMask::class,
            ['randomDataGenerator' => new Random()]
        );
    }

    public function testBeforeSave()
    {
        $this->quoteIdMask->beforeSave();
        $this->assertNotNull($this->quoteIdMask->getMaskedId(), 'Masked identifier is not generated.');
    }
}
