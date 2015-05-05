<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

/**
 * Backend Application which uses Magento Backend authentication process
 */
class BackendAppList
{
    /**
     * @var BackendApp[]
     */
    private $backendApps = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $backendApps
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        array $backendApps = []
    ) {
        $this->backendApps = $backendApps;
        $this->request = $request;

    }

    /**
     * Get Backend app based on its name
     *
     * @return BackendApp|null
     */
    public function getBackendApp()
    {
        $appName = $this->request->getQuery('app');
        if ($appName === null) {
            return;
        }
        if (isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }
}