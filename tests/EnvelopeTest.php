<?php

namespace Bernard\Tests;

use Bernard\Message\DefaultMessage;
use Bernard\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{
    public function testItWrapsAMessageWithMetadata()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals('Bernard\Message\DefaultMessage', $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertSame($message, $envelope->getMessage());
    }

    public function testNotDelayedMetadata()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'));
        $this->assertFalse($envelope->isDelayed());
        $this->assertEquals(0, $envelope->getDelay());
    }

    public function testDelayedMetadata()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'), 10);
        $this->assertTrue($envelope->isDelayed());
        $this->assertEquals(10, $envelope->getDelay());
    }

    /**
     * @expectedException Bernard\Exception\InvalidOperationException
     * @expectedExceptionMessage Delay must be greater or equal than zero
     */
    public function testNegativeDelay()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'), -10);
    }
}
