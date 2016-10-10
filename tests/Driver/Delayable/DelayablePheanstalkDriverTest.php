<?php
namespace Bernard\Tests\Driver\Delayable;

use Bernard\Driver\Delayable\DelayablePheanstalkDriver;
use Pheanstalk\PheanstalkInterface;

class DelayablePheanstalkDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pheanstalk = $this->getMockBuilder('Pheanstalk\Pheanstalk')
            ->setMethods(array(
                'putInTube'
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new DelayablePheanstalkDriver($this->pheanstalk);
    }

    public function testItPushesMessagesWithDelay()
    {
        $this->pheanstalk
            ->expects($this->once())
            ->method('putInTube')
            ->with(
                $this->equalTo('my-queue'),
                $this->equalTo('This is a message'),
                $this->equalTo(PheanstalkInterface::DEFAULT_PRIORITY),
                $this->equalTo(10)
            );

        $this->driver->pushMessageWithDelay('my-queue', 'This is a message', 10);
    }
}