<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\AdminAccountFactory;
use PHPUnit\Framework\TestCase;

class AdminAccountFactoryTest extends TestCase
{
    public function testCreate()
    {
        $serviceLocatorMock =
            $this->getMockForAbstractClass(ServiceLocatorInterface::class, ['get']);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(Encryptor::class)
            ->willReturn($this->getMockForAbstractClass(EncryptorInterface::class));
        $adminAccountFactory = new AdminAccountFactory($serviceLocatorMock);
        $adminAccount = $adminAccountFactory->create(
            $this->getMockForAbstractClass(AdapterInterface::class),
            []
        );
        $this->assertInstanceOf(AdminAccount::class, $adminAccount);
    }
}
