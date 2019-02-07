<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

/**
 * Class ErrorHandler
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    private $clientLogger;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    private $serverLogger;

    /**
     * @var array
     */
    private $clientErrorCategories;

    /**
     * @var array
     */
    private $serverErrorCategories;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    private $generalLogger;

    /**
     * ErrorHandler constructor.
     *
     * @param \Magento\Framework\Logger\Monolog $clientLogger
     * @param \Magento\Framework\Logger\Monolog $serverLogger
     * @param \Magento\Framework\Logger\Monolog $generalLogger
     * @param array                             $clientErrorCategories
     * @param array                             $serverErrorCategories
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        \Magento\Framework\Logger\Monolog $clientLogger,
        \Magento\Framework\Logger\Monolog $serverLogger,
        \Magento\Framework\Logger\Monolog $generalLogger,
        array $clientErrorCategories = [],
        array $serverErrorCategories = []
    ) {
        $this->clientLogger = $clientLogger;
        $this->serverLogger = $serverLogger;
        $this->generalLogger = $generalLogger;
        $this->clientErrorCategories = $clientErrorCategories;
        $this->serverErrorCategories = $serverErrorCategories;
    }

    /**
     * Handle errors
     *
     * @param \GraphQL\Error\Error[] $errors
     * @param callable               $formatter
     *
     * @return array
     */
    public function handle(array $errors, callable $formatter):array
    {
        return array_map(
            function (\GraphQL\Error\ClientAware $error) use ($formatter) {
                $this->logError($error);

                return $formatter($error);
            },
            $errors
        );
    }

    /**
     * @param \GraphQL\Error\ClientAware $error
     *
     * @return boolean
     */
    private function logError(\GraphQL\Error\ClientAware $error):bool
    {
        if (in_array($error->getCategory(), $this->clientErrorCategories)) {
            return $this->clientLogger->error($error);
        } elseif (in_array($error->getCategory(), $this->serverErrorCategories)) {
            return $this->serverLogger->error($error);
        }

        return $this->generalLogger->error($error);
    }
}
