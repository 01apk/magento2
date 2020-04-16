<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest;

class NewActionTest extends IntegrationTest
{
    public function testNewAction()
    {
        $this->_verifyLoadAndRenderLayout();
        // verify the request is forwarded to 'edit' action
        $this->_requestMock->expects($this->any())
            ->method('setActionName')
            ->with('edit')
            ->will($this->returnValue($this->_requestMock));
        $integrationContr = $this->_createIntegrationController('NewAction');
        $result = $integrationContr->execute();
        $this->assertNull($result);
    }
}
