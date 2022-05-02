<?php

namespace FluxEco\Projection\Adapters\MessageServer;
use  FluxEco\Projection;
use FluxEco\MessageServer;
use fluxValueObject;

class MessageServerApi  implements MessageServer\MessageServerApi {

    private Projection\Api $api;

    private function __construct(Projection\Api $api) {
        $this->api = $api;
    }

    public static function new(): self {
        return new self(Projection\Api::newFromEnv());
    }

    public function publish(string $channel, string $jsonMessage) : void
    {
        $message = fluxValueObject\getMessageFromJson($jsonMessage);
        $this->api->receiveAggregateRootStatePublished($message->getHeaders()['aggregateId'], $message->getHeaders()['aggregateName'],$message->getMessageName(), $message->getPayload());
        $response = fluxValueObject\getNewMessage($message->getCorrelationId(), $message->getMessageName().' handled', '');
        print_r($response);
    }
}