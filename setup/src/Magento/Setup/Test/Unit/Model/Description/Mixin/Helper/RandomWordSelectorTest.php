<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description\Mixin\Helper;

use Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector;
use PHPUnit\Framework\TestCase;

class RandomWordSelectorTest extends TestCase
{
    /**
     * @var RandomWordSelector
     */
    private $helper;

    public function setUp(): void
    {
        $this->helper = new RandomWordSelector();
    }

    /**
     * @param string $fixtureSource
     * @param int $fixtureCount
     * @dataProvider getTestData
     */
    public function testRandomSelector($fixtureSource, $fixtureCount)
    {
        $randWords = $this->helper->getRandomWords($fixtureSource, $fixtureCount);

        $this->assertCount($fixtureCount, $randWords);

        $fixtureWords = str_word_count($fixtureSource, 1);
        foreach ($randWords as $randWord) {
            $this->assertTrue(in_array($randWord, $fixtureWords));
        }
    }

    /**
     * @return array
     */
    public function getTestData()
    {
        return [
            [
                'source' => '
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                    sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                ',
                'count' => 1
            ],
            [
                'source' => 'Lorem.',
                'count' => 5
            ],
            [
                'source' => '
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                    sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                ',
                'count' => 3
            ],
        ];
    }
}
