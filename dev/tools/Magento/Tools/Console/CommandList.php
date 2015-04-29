<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Console;

/**
 * Class CommandList contains predefined list of command classes for Tools
 *
 * @package Magento\Tools\Console
 */
class CommandList
{
    /**
     * Gets list of tools command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            'Magento\Tools\Console\Command\DependenciesShowFrameworkCommand',
            'Magento\Tools\Console\Command\DependenciesShowModulesCircularCommand',
            'Magento\Tools\Console\Command\DependenciesShowModulesCommand',
        ];
    }

    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = new $class;
            } else {
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
