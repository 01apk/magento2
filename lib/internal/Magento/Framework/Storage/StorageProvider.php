<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Storage\AdapterFactory\LocalFactory;
use Magento\Framework\Storage\StorageFactory;

/**
 * Main entry point for accessing file storage
 *
 * See README.md for usage details
 */
class StorageProvider
{
    private $storageConfig = [];

    private $storage = [];

    /**
     * @var StorageFactory
     */
    private $storageFactory;

    /**
     * @var StorageAdapterProvider
     */
    private $adapterProvider;

    /**
     * Constructor
     *
     * @param StorageAdapterProvider $adapterProvider
     * @param \Magento\Framework\Storage\StorageFactory $storageFactory
     * @param array $storage
     * @param DeploymentConfig $envConfig
     */
    public function __construct(
        StorageAdapterProvider $adapterProvider,
        StorageFactory $storageFactory,
        array $storage,
        DeploymentConfig $envConfig
    ) {
        foreach ($storage as $storageName => $localPath) {
            $this->storageConfig[$storageName] = [
                'adapter' => LocalFactory::ADAPTER_NAME,
                'options' => [
                    'root' => BP . '/' . $localPath,
                ],
            ];
            $envStorageConfig = $envConfig->get('storage/' . $storageName);
            if ($envStorageConfig) {
                $this->storageConfig[$storageName] = array_replace(
                    $this->storageConfig[$storageName],
                    $envStorageConfig
                );
            }
        }
        $this->storageFactory = $storageFactory;
        $this->adapterProvider = $adapterProvider;
    }

    /**
     * Get storage by its name
     *
     * @param string $storageName
     * @return StorageInterface
     */
    public function get(string $storageName): StorageInterface
    {
        if (!isset($this->storage[$storageName])) {
            if (isset($this->storageConfig[$storageName])) {
                $config = $this->storageConfig[$storageName];
                if (empty($config['adapter']) || empty($config['options'])) {
                    throw new InvalidStorageConfigurationException(
                        "Incorrect configuration for storage '$storageName': required field " .
                        "'adapter' and/or 'options' is not defined"
                    );
                }
                $adapter = $this->adapterProvider->create($config['adapter'], $config['options']);
                $this->storage[$storageName] = $this->storageFactory->create(['adapter' => $adapter]);
            } else {
                throw new UnsupportedStorageException("No storage with name '$storageName' is declared");
            }
        }
        return $this->storage[$storageName];
    }
}
