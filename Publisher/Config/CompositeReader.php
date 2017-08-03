<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Composite reader for publisher config.
 * @since 2.2.0
 */
class CompositeReader implements ReaderInterface
{
    /**
     * Config validator.
     *
     * @var ValidatorInterface
     * @since 2.2.0
     */
    private $validator;

    /**
     * Config reade list.
     *
     * @var ReaderInterface[]
     * @since 2.2.0
     */
    private $readers;

    /**
     * @var DefaultValueProvider
     * @since 2.2.0
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface $validator
     * @param DefaultValueProvider $defaultValueProvider
     * @param ReaderInterface[] $readers
     * @since 2.2.0
     */
    public function __construct(
        ValidatorInterface $validator,
        DefaultValueProvider $defaultValueProvider,
        array $readers
    ) {
        $this->validator = $validator;
        $this->readers = $readers;
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * Read config.
     *
     * @param string|null $scope
     * @return array
     * @since 2.2.0
     */
    public function read($scope = null)
    {
        $result = [];
        foreach ($this->readers as $reader) {
            $result = array_replace_recursive($result, $reader->read($scope));
        }

        $result = $this->addDefaultConnection($result);

        $this->validator->validate($result);

        foreach ($result as $key => &$value) {
            //Find enabled connection
            $connection = null;
            foreach ($value['connections'] as $connectionConfig) {
                if (!$connectionConfig['disabled']) {
                    $connection = $connectionConfig;
                    break;
                }
            }
            $value['connection'] = $connection;
            unset($value['connections']);
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Add default connection.
     *
     * @param array $config
     * @return array
     * @since 2.2.0
     */
    private function addDefaultConnection(array $config)
    {
        $defaultConnectionName = $this->defaultValueProvider->getConnection();
        $default = [
            'name' => $defaultConnectionName,
            'exchange' => $this->defaultValueProvider->getExchange(),
            'disabled' => false,
        ];

        foreach ($config as &$value) {
            if (!isset($value['connections']) || empty($value['connections'])) {
                $value['connections'][$defaultConnectionName] = $default;
                continue;
            }

            $hasActiveConnection = false;
            /** Find enabled connection */
            foreach ($value['connections'] as $connectionConfig) {
                if (!$connectionConfig['disabled']) {
                    $hasActiveConnection = true;
                    break;
                }
            }
            if (!$hasActiveConnection) {
                $value['connections'][$defaultConnectionName] = $default;
            }
        }
        return $config;
    }
}
