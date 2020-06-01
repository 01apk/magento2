<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\TestFramework\Cms\Model\CustomLayoutManager;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var PageModelFactory $pageFactory */
$pageFactory = $objectManager->get(PageModelFactory::class);
/** @var CustomLayoutManager $fakeManager */
$fakeManager = $objectManager->get(CustomLayoutManager::class);
$layoutRepo = $objectManager->create(PageModel\CustomLayoutRepositoryInterface::class, ['manager' => $fakeManager]);

$customLayoutName = 'page_custom_layout';

/**
 * @var PageModel $page
 * @var PageRepositoryInterface $pageRepository
 */
$page = $pageFactory->create(['customLayoutRepository' => $layoutRepo]);
$pageRepository = $objectManager->create(PageRepositoryInterface::class);

$page = $pageRepository->getById('home');
$cmsPageId = (int)$page->getId();

$fakeManager->fakeAvailableFiles($cmsPageId, [$customLayoutName]);
$page->setData('layout_update_selected', $customLayoutName);
$pageRepository->save($page);
