<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetContentWithAssetsInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to return media asset list for the specified asset.
 */
class GetContentWithAssets implements GetContentWithAssetsInterface
{
    private const MEDIA_CONTENT_ASSET_TABLE_NAME = 'media_content_asset';
    private const ASSET_ID = 'asset_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $factory;

    /**
     * @param ContentIdentityInterfaceFactory $factory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContentIdentityInterfaceFactory $factory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $assetIds): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME))
                ->where(self::ASSET_ID . 'IN (?)', $assetIds);

            $contentIdentities = [];
            foreach ($connection->fetchAssoc($select) as $contentIdentityData) {
                $contentIdentities[] = $this->factory->create(['data' => $contentIdentityData]);
            }
            return $contentIdentities;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new IntegrationException(
                __('An error occurred at getting media asset to content relation by media asset id.')
            );
        }
    }
}
