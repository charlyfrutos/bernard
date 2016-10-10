<?php
namespace Bernard;

/**
 * @package Bernard
 * @author Carlos Frutos <charly@workana.com>
 */
interface DelayableDriver extends Driver
{
    /**
     * Insert a message with delay
     *
     * @param string    $queueName
     * @param string    $message
     * @param int       $delay         Delay in seconds
     */
    public function pushMessageWithDelay($queueName, $message, $delay);
}