<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
$websiteId = $website->load('test', 'code')->getId();
if ($websiteId) {
    $website->delete();
}

$storeGroup = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Group::class);
/** @var $storeGroup \Magento\Store\Model\Group */
$storeGroupId = $storeGroup->load('fixture_second_store_group', 'code')->getId();
if ($storeGroupId) {
    $storeGroup->delete();
}

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store->load('fixture_second_store', 'code')->getId()) {
    $store->delete();
}

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store->load('fixture_third_store', 'code')->getId()) {
    $store->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
