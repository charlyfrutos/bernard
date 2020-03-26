<?php

namespace Bernard\Queue;

use Bernard\Driver;
use Bernard\DelayableDriver;
use Bernard\Envelope;
use Bernard\Serializer;
use Bernard\Exception\InvalidOperationException;

/**
 * @package Bernard
 */
class PersistentQueue extends AbstractQueue
{
    protected $driver;
    protected $serializer;
    protected $receipts;

    /**
     * @param string     $name
     * @param Driver     $driver
     * @param Serializer $serializer
     */
    public function __construct($name, Driver $driver, Serializer $serializer)
    {
        parent::__construct($name);

        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->receipts = new \SplObjectStorage();

        $this->register();
    }

    /**
     * Register with the driver
     */
    public function register()
    {
        $this->errorIfClosed();

        $this->driver->createQueue($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->driver->countMessages($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        parent::close();

        $this->driver->removeQueue($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        if ($envelope->isDelayed()) {
            $this->assertDriverIsDelayable();
            $this->driver->pushMessageWithDelay(
                $this->name,
                $this->serializer->serialize($envelope),
                $envelope->getDelay()
            );
        } else {
            $this->driver->pushMessage($this->name, $this->serializer->serialize($envelope));
        }
    }

    private function assertDriverIsDelayable()
    {
        if (!($this->driver instanceof DelayableDriver)) {
            throw new InvalidOperationException('This driver can\'t manage delayed messages');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();

        if ($this->receipts->contains($envelope)) {
            $this->driver->acknowledgeMessage($this->name, $this->receipts[$envelope]);

            $this->receipts->detach($envelope);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param int $duration Number of seconds to keep polling for messages.
     */
    public function dequeue($duration = 5)
    {
        $this->errorIfClosed();

        list($serialized, $receipt) = $this->driver->popMessage($this->name, $duration);

        if ($serialized) {
            $envelope = $this->serializer->unserialize($serialized);

            $this->receipts->attach($envelope, $receipt);

            return $envelope;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();

        $messages = $this->driver->peekQueue($this->name, $index, $limit);

        return array_map(array($this->serializer, 'unserialize'), $messages);
    }
}
