<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\App\Bootstrap;

class InstallerFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Factory method for installer object
     *
     * @param LoggerInterface $log
     * @return Installer
     */
    public function create(LoggerInterface $log)
    {
        $params = $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
        return new Installer(
            $this->serviceLocator->get('Magento\Setup\Model\FilePermissions'),
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig\Writer'),
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig'),
            $this->serviceLocator->get('Magento\Setup\Module\SetupFactory'),
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList'),
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList\Loader'),
            $this->serviceLocator->get('Magento\Framework\App\Filesystem\DirectoryList'),
            $this->serviceLocator->get('Magento\Setup\Model\AdminAccountFactory'),
            $log,
            $this->serviceLocator->get('Magento\Framework\Math\Random'),
            $this->serviceLocator->get('Magento\Setup\Module\ConnectionFactory'),
            $this->serviceLocator->get('Magento\Framework\App\MaintenanceMode'),
            $this->serviceLocator->get('Magento\Framework\Filesystem'),
            $this->serviceLocator,
            $this->serviceLocator->get('Magento\Setup\Model\SampleData'),
            Bootstrap::createObjectManagerFactory(BP, $params)
        );
    }
}
