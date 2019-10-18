<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Template;

/**
 * Class FilterEmulate
 *
 * @package Magento\Widget\Model\Template
 */
class FilterEmulate extends Filter
{
    /**
     * Generate widget with emulation frontend area
     *
     * @param string[] $construction
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function widgetDirective($construction)
    {
        return $this->_appState->emulateAreaCode('frontend', [$this, 'generateWidget'], [$construction]);
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws \Exception
     */
    public function filter($value) : string
    {
        return $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            [$this, 'parent::filter'],
            [$value]
        );
    }
}
