<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Framework\Module\DataSetup */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$installer->appendClassAliasReplace(
    'widget_instance',
    'instance_type',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['instance_id']
);

$installer->appendClassAliasReplace(
    'layout_update',
    'xml',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    ['layout_update_id']
);

$installer->doUpdateClassAliases();

$installer->endSetup();
