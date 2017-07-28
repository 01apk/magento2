<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Psr\Log\LoggerInterface;

/**
 * Queue based on MessageQueue protocol
 * @since 2.0.0
 */
class Queue implements QueueInterface
{
    /**
     * @var QueueManagement
     * @since 2.0.0
     */
    private $queueManagement;

    /**
     * @var EnvelopeFactory
     * @since 2.0.0
     */
    private $envelopeFactory;

    /**
     * @var string
     * @since 2.0.0
     */
    private $queueName;

    /**
     * @var int
     * @since 2.0.0
     */
    private $interval;

    /**
     * @var int
     * @since 2.0.0
     */
    private $maxNumberOfTrials;

    /**
     * @var LoggerInterface $logger
     * @since 2.1.0
     */
    private $logger;

    /**
     * Queue constructor.
     *
     * @param QueueManagement $queueManagement
     * @param EnvelopeFactory $envelopeFactory
     * @param LoggerInterface $logger
     * @param string $queueName
     * @param int $interval
     * @param int $maxNumberOfTrials
     * @since 2.0.0
     */
    public function __construct(
        QueueManagement $queueManagement,
        EnvelopeFactory $envelopeFactory,
        LoggerInterface $logger,
        $queueName,
        $interval = 5,
        $maxNumberOfTrials = 3
    ) {
        $this->queueManagement = $queueManagement;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->interval = $interval;
        $this->maxNumberOfTrials = $maxNumberOfTrials;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function dequeue()
    {
        $envelope = null;
        $messages = $this->queueManagement->readMessages($this->queueName, 1);
        if (isset($messages[0])) {
            $properties = $messages[0];

            $body = $properties[QueueManagement::MESSAGE_BODY];
            unset($properties[QueueManagement::MESSAGE_BODY]);

            $envelope = $this->envelopeFactory->create(['body' => $body, 'properties' => $properties]);
        }

        return $envelope;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        $this->queueManagement->changeStatus($relationId, QueueManagement::MESSAGE_STATUS_COMPLETE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function subscribe($callback)
    {
        while (true) {
            while ($envelope = $this->dequeue()) {
                try {
                    call_user_func($callback, $envelope);
                    $this->acknowledge($envelope);
                } catch (\Exception $e) {
                    $this->reject($envelope);
                }
            }
            sleep($this->interval);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        if ($properties[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < $this->maxNumberOfTrials && $requeue) {
            $this->queueManagement->pushToQueueForRetry($relationId);
        } else {
            $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
            if ($rejectionMessage !== null) {
                $this->logger->critical(__('Message has been rejected: %1', $rejectionMessage));
            }
        }
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    public function push(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $this->queueManagement->addMessageToQueues(
            $properties[QueueManagement::MESSAGE_TOPIC],
            $envelope->getBody(),
            [$this->queueName]
        );
    }
}
