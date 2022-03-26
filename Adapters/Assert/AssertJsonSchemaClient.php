<?php

namespace FluxEco\Projection\Adapters\Assert;
use Flux\Eco\Assert\Adapters\Api;
use FluxEco\Projection\Core\Ports;

class AssertJsonSchemaClient implements  Ports\Assert\AssertJsonSchemaClient
{

    private Api\AssertApi $assertApi;

    private function __construct(Api\AssertApi $assertApi)
    {
        $this->assertApi = $assertApi;
    }

    public static function new(): self
    {
        $assertApi = Api\AssertApi::new();
        return new self($assertApi);
    }

    /**
     * @throws Exception
     */
    /*
    final public function assertPropertyExistsInSchema(AssertPropertyExistsInSchema $assertPropertyExistsInSchema): void
    {
        $this->jsonSchemaAsserters->assertPropertyExistsInSchema($assertPropertyExistsInSchema->getPropertyKey(), $assertPropertyExistsInSchema->getPropertyValue(), $assertPropertyExistsInSchema->getJsonSchemaYamlFilePath());
    }


    final public function assertPropertyIsEqual(AssertPropertyIsEqual $assertPropertyIsEqual): void
    {
        $this->jsonSchemaAsserters->assertPropertyIsEqual($assertPropertyIsEqual->getPropertyKey(), $assertPropertyIsEqual->getPropertyValue(), $assertPropertyIsEqual->getCurrentPropertyState(), $assertPropertyIsEqual->getJsonSchemaYamlFilePath());
    }*/

    public function assert(\JsonSerializable $value, array $jsonSchema): void
    {
        $this->assertApi->jsonSchema()->assertProperties($value, $jsonSchema);
    }
}