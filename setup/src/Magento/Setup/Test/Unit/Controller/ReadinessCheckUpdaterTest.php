<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\ReadinessCheckUpdater;
use PHPUnit\Framework\TestCase;

class ReadinessCheckUpdaterTest extends TestCase
{
    /**
     * @var ReadinessCheckUpdater
     */
    private $controller;

    public function setUp(): void
    {
        $this->controller = new ReadinessCheckUpdater();
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('actionFrom', $variables);
        $this->assertEquals('updater', $variables['actionFrom']);
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/readiness-check/progress.phtml', $viewModel->getTemplate());
    }
}
