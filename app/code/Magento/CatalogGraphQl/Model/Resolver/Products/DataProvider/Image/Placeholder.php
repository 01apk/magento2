<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Image Placeholder provider
 */
class Placeholder
{
    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var Theme
     */
    private $theme;

    /**
     * Placeholder constructor.
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     * @param Theme $theme
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository,
        Theme $theme
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->assetRepository = $assetRepository;
        $this->theme = $theme;
    }

    /**
     * Get placeholder
     *
     * @param string $imageType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPlaceholder(string $imageType): string
    {
        $imageAsset = $this->placeholderFactory->create(['type' => $imageType]);

        // check if placeholder defined in config
        if ($imageAsset->getFilePath()) {
            return $imageAsset->getUrl();
        }

        $themeData = $this->theme->getThemeData();
        return $this->assetRepository->createAsset(
            "Magento_Catalog::images/product/placeholder/{$imageType}.jpg",
            $themeData
        )->getUrl();
    }
}
