<?php
namespace PSB\Core;

/**
 * @codeCoverageIgnore
 */
class OutgoingOptionsFactory
{
    /**
     * @return SendOptions
     */
    public function createSendOptions()
    {
        return new SendOptions();
    }

    /**
     * @return PublishOptions
     */
    public function createPublishOptions()
    {
        return new PublishOptions();
    }

    /**
     * @return ReplyOptions
     */
    public function createReplyOptions()
    {
        return new ReplyOptions();
    }

    /**
     * @return SubscribeOptions
     */
    public function createSubscribeOptions()
    {
        return new SubscribeOptions();
    }

    /**
     * @return UnsubscribeOptions
     */
    public function createUnsubscribeOptions()
    {
        return new UnsubscribeOptions();
    }
}
