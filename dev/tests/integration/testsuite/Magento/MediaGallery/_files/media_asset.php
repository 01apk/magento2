<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Model\Asset\Command\SaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AssetInterfaceFactory $mediaAssetFactory */
$mediaAssetFactory = $objectManager->get(AssetInterfaceFactory::class);
/** @var AssetInterface $mediaAsset */
$mediaAsset = $mediaAssetFactory->create(
    [
        'data' => [
            'id' => 1,
            'path' => 'testDirectory/path.jpg'
        ]
    ]
);
/** @var SaveInterface $mediaSave */
$mediaSave = $objectManager->get(SaveInterface::class);
$mediaId = $mediaSave->execute($mediaAsset);

