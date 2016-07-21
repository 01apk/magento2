<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;

/**
 * Instances of this class represent config binding binging items declared in etc/queue_topology.xsd
 */
class Binding implements BindingInterface
{
    /**
     * Binding id.
     *
     * @var string
     */
    private $id;

    /**
     * Binding destination type.
     *
     * @var string
     */
    private $destinationType;

    /**
     * Binding destination.
     *
     * @var string
     */
    private $destination;

    /**
     * Flag. Is binding disabled.
     * @var bool
     */
    private $isDisabled;

    /**
     * Topic name.
     *
     * @var string
     */
    private $topic;

    /**
     * Binding arguments.
     *
     * @var array
     */
    private $arguments;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationType()
    {
        return $this->destinationType;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        // TODO: Implement getArguments() method.
    }

    /**
     * Set binding data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->id = $data['id'];
        $this->destinationType = $data['destinationType'];
        $this->destination = $data['destination'];
        $this->arguments = $data['arguments'];
        $this->topic = $data['topic'];
        $this->isDisabled = $data['disabled'];
    }
}
