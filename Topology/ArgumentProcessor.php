<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

/**
 * @deprecated
 * see: https://github.com/php-amqplib/php-amqplib/issues/405
 */
trait ArgumentProcessor
{
    /**
     * Process arguments
     *
     * @param array $arguments
     * @return array
     */
    function processArguments($arguments)
    {
        $output = [];
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $output[$key] = ['A', $value];
            } elseif (is_int($value)) {
                $output[$key] = ['I', $value];
            } elseif (is_bool($value)) {
                $output[$key] = ['t', $value];
            } elseif (is_string($value)) {
                $output[$key] = ['S', $value];
            } else {
                throw new \InvalidArgumentException('Unknown argument type ' . gettype($value));
            }
        }
        return $output;
    }
}
