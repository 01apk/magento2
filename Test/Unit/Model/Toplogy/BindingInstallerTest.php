<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Test\Unit\Model\Topology;

use Magento\Amqp\Model\Topology\BindingInstaller;
use Magento\Amqp\Model\Topology\BindingInstallerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class BindingInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstall()
    {
        $installerOne = $this->getMock(BindingInstallerInterface::class);
        $installerTwo = $this->getMock(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);
        $binding = $this->getMock(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('queue');
        $installerOne->expects($this->once())->method('install')->with($channel, $binding, 'magento');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Installer type [test] is not configured
     */
    public function testInstallInvalidType()
    {
        $installerOne = $this->getMock(BindingInstallerInterface::class);
        $installerTwo = $this->getMock(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->getMock(AMQPChannel::class, [], [], '', false, false);
        $binding = $this->getMock(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('test');
        $installerOne->expects($this->never())->method('install');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }
}
