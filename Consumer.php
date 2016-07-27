<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\QueueRepository;

/**
 * Class Consumer used to process a single message, unlike batch consumer.
 *
 * This could be used for both synchronous and asynchronous processing, depending on topic.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Consumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var CallbackInvoker
     */
    private $invoker;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvoker $invoker
     * @param MessageEncoder $messageEncoder
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CallbackInvoker $invoker,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke($queue, $maxNumberOfMessages, $this->getTransactionCallback($queue));
        }
    }

    /**
     * Decode message and invoke callback method, return reply back for sync processing.
     *
     * @param EnvelopeInterface $message
     * @param boolean $isSync
     * @return string|null
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message, $isSync = false)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->configuration->getMessageSchemaType($topicName);
            if ($messageSchemaType == CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD) {
                foreach ($handlers as $callback) {
                    $result = call_user_func_array($callback, $decodedMessage);
                    return $this->processSyncResponse($topicName, $result);
                }
            } else {
                foreach ($handlers as $callback) {
                    $result = call_user_func($callback, $decodedMessage);
                    if ($isSync) {
                        return $this->processSyncResponse($topicName, $result);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Validate and encode synchronous handler output.
     *
     * @param string $topicName
     * @param mixed $result
     * @return string
     * @throws LocalizedException
     */
    private function processSyncResponse($topicName, $result)
    {
        if (isset($result)) {
            $this->getMessageValidator()->validate($topicName, $result, false);
            return $this->messageEncoder->encode($topicName, $result, false);
        } else {
            throw new LocalizedException(new Phrase('No reply message resulted in RPC.'));
        }
    }

    /**
     * Send RPC response message.
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    private function sendResponse(EnvelopeInterface $envelope)
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->getConsumerConfig()
            ->getConsumer($this->configuration->getConsumerName())->getConnection();
        $queue = $this->getQueueRepository()->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }

    /**
     * Get transaction callback. This handles the case of both sync and async.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            try {
                $this->resource->getConnection()->beginTransaction();
                $topicName = $message->getProperties()['topic_name'];
                $topicConfig = $this->getCommunicationConfig()->getTopic($topicName);
                $this->getMessageController()->lock($message, $this->configuration->getConsumerName());

                if ($topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]) {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->getEnvelopeFactory()->create(
                        ['body' => $responseBody, 'properties' => $message->getProperties()]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->configuration->getTopicNames();
                    if (in_array($topicName, $allowedTopics)) {
                        $this->dispatchMessage($message);
                    } else {
                        $queue->reject($message);
                        return;
                    }
                }
                $this->resource->getConnection()->commit();
                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
                $this->resource->getConnection()->rollBack();
                $queue->acknowledge($message);
            } catch (\Magento\Framework\MessageQueue\ConnectionLostException $e) {
                $this->resource->getConnection()->rollBack();
            } catch (\Exception $e) {
                $this->resource->getConnection()->rollBack();
                $queue->reject($message, false, $e->getMessage());
            }
        };
    }

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CommunicationConfig::class);
        }
        return $this->communicationConfig;
    }

    /**
     * Get queue repository.
     *
     * @return QueueRepository
     *
     * @deprecated
     */
    private function getQueueRepository()
    {
        if ($this->queueRepository === null) {
            $this->queueRepository = \Magento\Framework\App\ObjectManager::getInstance()->get(QueueRepository::class);
        }
        return $this->queueRepository;
    }
    
    /**
     * Get message controller.
     *
     * @return MessageController
     *
     * @deprecated
     */
    private function getMessageController()
    {
        if ($this->messageController === null) {
            $this->messageController = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(MessageController::class);
        }
        return $this->messageController;
    }
    
    /**
     * Get message validator.
     *
     * @return MessageValidator
     *
     * @deprecated
     */
    private function getMessageValidator()
    {
        if ($this->messageValidator === null) {
            $this->messageValidator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(MessageValidator::class);
        }
        return $this->messageValidator;
    }
    
    /**
     * Get envelope factory.
     *
     * @return EnvelopeFactory
     *
     * @deprecated
     */
    private function getEnvelopeFactory()
    {
        if ($this->envelopeFactory === null) {
            $this->envelopeFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(EnvelopeFactory::class);
        }
        return $this->envelopeFactory;
    }
}
