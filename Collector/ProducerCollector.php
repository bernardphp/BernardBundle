<?php

namespace Bernard\BernardBundle\Collector;

use Bernard\BernardEvents;
use Bernard\Event\EnvelopeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ProducerCollector extends DataCollector implements EventSubscriberInterface
{
    public function __construct()
    {
        $this->data = [
            'messageCount' => 0,
            'messages' => [],
        ];
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        // We don't collect anything from the request data
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'bernard';
    }

    public function onBernardProduce(EnvelopeEvent $event)
    {
        $envelope = $event->getEnvelope();

        $this->data['messages'][(string) $event->getQueue()][] = [
            'name' => $envelope->getName(),
            'message' => $this->cloneVar($envelope->getMessage()),
            'timestamp' => $envelope->getTimestamp(),
            'class' => $envelope->getClass(),
        ];

        ++$this->data['messageCount'];
    }

    public static function getSubscribedEvents()
    {
        return array(
            BernardEvents::PRODUCE => 'onBernardProduce',
        );
    }

    public function getMessageCount()
    {
        return $this->data['messageCount'];
    }

    public function getDistinctQueueCount()
    {
        return count($this->data['messages']);
    }

    public function getAllMessages()
    {
        return $this->data['messages'];
    }

    /**
     * Resets this data collector to its initial state.
     */
    public function reset()
    {
        $this->data = [
            'messageCount' => 0,
            'messages' => [],
        ];
    }
}
