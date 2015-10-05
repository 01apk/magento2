<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Setup;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\App\DeploymentConfig;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_QUEUE_RABBITMQ_HOST = 'rabbitmq-host';
    const INPUT_KEY_QUEUE_RABBITMQ_PORT = 'rabbitmq-port';
    const INPUT_KEY_QUEUE_RABBITMQ_USER = 'rabbitmq-user';
    const INPUT_KEY_QUEUE_RABBITMQ_PASSWORD = 'rabbitmq-password';
    const INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST = 'rabbitmq-virtualhost';
    const INPUT_KEY_QUEUE_RABBITMQ_SSL = 'rabbitmq-ssl';

    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_QUEUE_RABBITMQ_HOST = 'queue/rabbit/host';
    const CONFIG_PATH_QUEUE_RABBITMQ_PORT = 'queue/rabbit/port';
    const CONFIG_PATH_QUEUE_RABBITMQ_USER = 'queue/rabbit/user';
    const CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD = 'queue/rabbit/password';
    const CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST = 'queue/rabbit/virtualhost';
    const CONFIG_PATH_QUEUE_RABBITMQ_SSL = 'queue/rabbit/ssl';

    /**
     * Default values
     */
    const DEFAULT_RABBITMQ_HOST = '';
    const DEFAULT_RABBITMQ_PORT = '';
    const DEFAULT_RABBITMQ_USER = '';
    const DEFAULT_RABBITMQ_PASSWORD = '';
    const DEFAULT_RABBITMQ_VIRTUAL_HOST = '/';
    const DEFAULT_RABBITMQ_SSL = '';

    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    /**
     * Constructor
     *
     * @param ConnectionValidator $connectionValidator
     */
    public function __construct(ConnectionValidator $connectionValidator)
    {
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_HOST,
                'RabbitMQ server host',
                self::DEFAULT_RABBITMQ_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_PORT,
                'RabbitMQ server port',
                self::DEFAULT_RABBITMQ_PORT
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_USER,
                'RabbitMQ server username',
                self::DEFAULT_RABBITMQ_USER
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD,
                'RabbitMQ server password',
                self::DEFAULT_RABBITMQ_PASSWORD
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST,
                'RabbitMQ virtualhost',
                self::DEFAULT_RABBITMQ_VIRTUAL_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_SSL,
                'RabbitMQ SSL',
                self::DEFAULT_RABBITMQ_SSL
            ),
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_HOST])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_HOST, $data[self::INPUT_KEY_QUEUE_RABBITMQ_HOST]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_PORT])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_PORT, $data[self::INPUT_KEY_QUEUE_RABBITMQ_PORT]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_USER])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_USER, $data[self::INPUT_KEY_QUEUE_RABBITMQ_USER]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD, $data[self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST])) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST,
                $data[self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST]
            );
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_SSL])) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_RABBITMQ_SSL,
                $data[self::INPUT_KEY_QUEUE_RABBITMQ_SSL]
            );
        }

        return [$configData];
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (isset($options[self::INPUT_KEY_QUEUE_RABBITMQ_HOST])
            && $options[self::INPUT_KEY_QUEUE_RABBITMQ_HOST] !== '') {

            $result = $this->connectionValidator->isConnectionValid(
                $options[self::INPUT_KEY_QUEUE_RABBITMQ_HOST],
                $options[self::INPUT_KEY_QUEUE_RABBITMQ_PORT],
                $options[self::INPUT_KEY_QUEUE_RABBITMQ_USER],
                $options[self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD],
                $options[self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST]
            );

            if (!$result) {
                $errors[] = "Could not connect to the RabbitMq Server.";
            }
        }

        return $errors;
    }
}
