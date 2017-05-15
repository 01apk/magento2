<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

/**
 * @api
 */
interface EsConfigInterface
{
    /**
     * @return array
     */
    public function getStemmerInfo();

    /**
     * @return array
     */
    public function getStopwordsInfo();
}
