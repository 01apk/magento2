<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Form;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testSetDataObject()
    {
        $form = new DataObject();
        $dataObject = new DataObject();

        // _prepateLayout() is blocked, because it is used by block to instantly add 'form' child
        $block = $this->createPartialMock(Container::class, ['getChildBlock']);
        $block->expects($this->once())->method('getChildBlock')->with('form')->will($this->returnValue($form));

        $block->setDataObject($dataObject);
        $this->assertSame($dataObject, $block->getDataObject());
        $this->assertSame($dataObject, $form->getDataObject());
    }
}
