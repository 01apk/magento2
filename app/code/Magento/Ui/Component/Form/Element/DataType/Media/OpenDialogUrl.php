<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element\DataType\Media;

/**
 * Basic configuration for OpenDialogUrl
 */
class OpenDialogUrl
{
    private const DEFAULT_OPEN_DIALOG_URL = 'cms/wysiwyg_images/index';

    /**
     * @var string
     */
    private $openDialogUrl;

    /**
     * Returns open dialog url for media browser
     *
     * @return string
     */
    public function get(): string
    {
        if ($this->openDialogUrl) {
            return $this->openDialogUrl;
        }
        return self::DEFAULT_OPEN_DIALOG_URL;
    }
}
