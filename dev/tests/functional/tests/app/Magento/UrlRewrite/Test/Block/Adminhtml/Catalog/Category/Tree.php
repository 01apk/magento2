<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Fixture\CatalogCategory;

/**
 * Categories tree block.
 */
class Tree extends Block
{
    /**
     * Locator value for skip category button.
     *
     * @var string
     */
    protected $skipCategoryButton = '[data-ui-id$="skip-categories"]';

    /**
     * Select category by its name.
     *
     * @param null|CatalogCategory $category
     * @return void
     */
    public function selectCategory($category)
    {
        if ($category != null && $category->hasData('name')) {
            $this->_rootElement->find(
                "//a[contains(text(),'{$category->getName()}')]",
                Locator::SELECTOR_XPATH
            )->click();
        } else {
            $this->skipCategorySelection();
        }
    }

    /**
     * Skip category selection.
     *
     * @return void
     */
    protected function skipCategorySelection()
    {
        $this->_rootElement->find($this->skipCategoryButton, Locator::SELECTOR_CSS)->click();
    }
}
