<?php

namespace FluxEco\Projection\Adapters\Api;

//todo bind to the API of AsyncApi Component

class AsyncApiClient
{
    const ASYNC_API_GET_ITEM_CHANNEL_PATH = '/api/v1/data/{projectionName}/item/{id}/getItem';

    private array $asyncApiSchema;

    private function __construct(
        array  $asyncApiSchema,
    )
    {
        $this->asyncApiSchema = $asyncApiSchema;
    }

    public static function new(): self
    {
        $asyncApiSchema = yaml_parse(file_get_contents(AsyncApiEnv::PARAM_ASYNCAPI_SCHEMA_FILE_PATH));
    }

    public function transformProjectionItemToResponse(
        string $projectionName,
        array $itemData
    ): array
    {
        $channel = $this->asyncApiSchema[self::ASYNC_API_GET_ITEM_CHANNEL_PATH];
        $projectionSchemas = $channel['publish']['x-projection_schemas']['oneOf'];
        foreach($projectionSchemas as $projectionSchemaRef) {
            if(str_contains($projectionSchemaRef['$ref'],$projectionName)) {

            }
        }
    }


}