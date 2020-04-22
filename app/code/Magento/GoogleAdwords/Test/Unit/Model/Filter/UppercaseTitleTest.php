<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Model\Filter;

use Magento\GoogleAdwords\Model\Filter\UppercaseTitle;
use PHPUnit\Framework\TestCase;

class UppercaseTitleTest extends TestCase
{
    /**
     * @var UppercaseTitle
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new UppercaseTitle();
    }

    /**
     * @return array
     */
    public function dataProviderForFilterValues()
    {
        return [['some name', 'Some Name'], ['test', 'Test']];
    }

    /**
     * @param string $inputValue
     * @param string $returnValue
     * @dataProvider dataProviderForFilterValues
     */
    public function testFilter($inputValue, $returnValue)
    {
        $this->assertEquals($returnValue, $this->_model->filter($inputValue));
    }
}
