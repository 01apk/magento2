<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App;

use Magento\Framework\App;
use Magento\Framework\App\Console\Response;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Compiler
 * @package Magento\Tools\Di\App
 *
 */
class Compiler implements \Magento\Framework\AppInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Task\Manager
     */
    private $taskManager;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param Task\Manager $taskManager
     * @param ObjectManagerInterface $objectManager
     * @param Response $response
     */
    public function __construct(
        Task\Manager $taskManager,
        ObjectManagerInterface $objectManager,
        Response $response
    ) {
        $this->taskManager = $taskManager;
        $this->objectManager = $objectManager;
        $this->response = $response;
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->objectManager->configure(
            [
                'preferences' =>
                [
                    'Magento\Tools\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Config\Writer\Filesystem',
                    'Magento\Tools\Di\Compiler\Log\Writer\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Log\Writer\Console'
                ],
                'Magento\Tools\Di\Compiler\Config\ModificationChain' => [
                    'arguments' => [
                        'modificationsList' => [
                            'BackslashTrim' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\BackslashTrim'],
                            'PreferencesResolving' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'InterceptorSubstitution' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\InterceptorSubstitution'],
                            'InterceptionPreferencesResolving' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'ArgumentsSerialization' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\ArgumentsSerialization'],
                        ]
                    ]
                ],
                'Magento\Tools\Di\Code\Generator\PluginList' => [
                    'cache' => [
                        'instance' => 'Magento\Framework\App\Interception\Cache\CompiledConfig'
                    ]
                ]
            ]
        );

        $operations = [
            Task\OperationFactory::REPOSITORY_GENERATOR => [
                'path' => BP . '/' . 'app/code',
                'filePatterns' => ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/']
            ],
            Task\OperationFactory::APPLICATION_CODE_GENERATOR => [
                BP . '/'  . 'app/code', BP . '/'  . 'lib/internal/Magento/Framework', BP . '/'  . 'var/generation'
            ],
            Task\OperationFactory::INTERCEPTION =>
                BP . '/var/generation',
            Task\OperationFactory::AREA_CONFIG_GENERATOR => [
                BP . '/' . 'app/code', BP . '/' . 'lib/internal/Magento/Framework', BP . '/' . 'var/generation'
            ],
            Task\OperationFactory::INTERCEPTION_CACHE => [
                BP . '/' . 'app/code', BP . '/' . 'lib/internal/Magento/Framework', BP . '/' . 'var/generation'
            ]
        ];

        $responseCode = Response::SUCCESS;
        try {
            foreach ($operations as $operationCode => $arguments) {
                $this->taskManager->addOperation(
                    $operationCode,
                    $arguments
                );
            }
            $this->taskManager->process();

        } catch (Task\OperationException $e) {
            $responseCode = Response::ERROR;
            $this->response->setBody($e->getMessage());
        }

        $this->response->setCode($responseCode);
        return $this->response;
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
