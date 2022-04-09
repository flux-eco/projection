# flux-eco/projection

This component is responsible for projections of aggregate root objects. The projections are described
as json schemas.

## Usage
.env
``` 
PROJECTION_APP_SCHEMA_DIRECTORY=../schemas
PROJECTION_ECO_SCHEMA_DIRECTORY=schemas/projections
PROJECTION_STORAGE_CONFIG_ENV_PREFIX=PROJECTION_
PROJECTION_STORAGE_NAME=projection
PROJECTION_STORAGE_HOST=localhost
PROJECTION_STORAGE_DRIVER=Pdo_Mysql
PROJECTION_STORAGE_USER=user
PROJECTION_STORAGE_PASSWORD=password
STREAM_STORAGE_CONFIG_ENV_PREFIX=STREAM_
STREAM_STORAGE_NAME=stream
STREAM_STORAGE_HOST=localhost
STREAM_STORAGE_DRIVER=Pdo_Mysql
STREAM_STORAGE_USER=user
STREAM_STORAGE_PASSWORD=password
STREAM_TABLE_NAME=stream
STREAM_STATE_SCHEMA_FILE=../vendor/flux-eco/global-stream/schemas/State.yaml
```

schemas\domain\account.yaml
``` 
title: account
type: object
properties:
    aggregateId:
        type: string
        readOnly: true
    correlationId:
        type: string
        readOnly: true
    aggregateName:
        type: string
        const: todo
        readOnly: true
    sequence:
        type: integer
        readOnly: true
    createdDateTime:
        type: string
        format: date-time
        readOnly: true
    createdBy:
        type: string
        format: email
        readOnly: true
    changedDateTime:
        type: string
        format: date-time
        readOnly: true
    changedBy:
        type: string
        format: email
        readOnly: true
    rootObjectSchema:
        type: string
        const: v1
    rootObject:
        type: object
        properties:
            firstname:
                type: string
            lastname:
                type: string
```

schemas\projections\account.yaml
```
title: account
type: object
aggregateRootNames:
  - account
properties:
  projectionId:
    type: string
  firstname:
    type: string
    index: account.rootObject.firstname
  lastname:
    type: string
    index: account.rootObject.lastname
```

example.php
```
<?php

require_once __DIR__ . '/../vendor/autoload.php';

FluxEco\DotEnv\Api::new()->load(__DIR__);

//initialize
fluxProjection\initialize();
echo "projection storage initialized".PHP_EOL.PHP_EOL;

//receive aggregateRootStatePublished
$aggregateRootId = fluxValueObject\getNewUuid();
$aggregateRootName = 'account';
$messageName = 'aggregateRootCreated';
$jsonAggregateRootSchema = json_encode(yaml_parse(file_get_contents('schemas/domain/account.yaml')));
$payload = json_encode([
    "firstname" => [
        "value" => "Emmett",
        "isEntityId" => false
    ],
    "lastname" => [
        "value" => "Brown",
        "isEntityId" => false
    ]
]);

fluxProjection\receiveAggregateRootStatePublished(
    $aggregateRootId,
    $aggregateRootName,
    $messageName,
    $jsonAggregateRootSchema,
    $payload
);
echo "aggregate root state published".PHP_EOL.PHP_EOL;

//get item list
echo "getItemList: ".PHP_EOL;
$projectionName = 'account';
$filter = [];
$itemList = fluxProjection\getItemList(
    $projectionName,
    $filter
);

print_r($itemList).PHP_EOL.PHP_EOL;


//getProjectionIdForAggregateId\
echo 'getProjectionIdForAggregateId '.PHP_EOL;

$projectionId = fluxProjection\getProjectionIdForAggregateId($projectionName, $aggregateRootId);

echo $projectionId.PHP_EOL.PHP_EOL;


//getItem
echo "getItem: ".PHP_EOL;

$item = fluxProjection\getItem($projectionName, $projectionId);

print_r($item).PHP_EOL.PHP_EOL;


//getAggregateRootMappingsForProjectionId
echo 'getAggregateRootMappingsForProjectionId '.PHP_EOL;

$mapping = fluxProjection\getAggregateRootMappingsForProjectionId($projectionId);

print_r($mapping).PHP_EOL.PHP_EOL;
```

output
```
projection storage initialized

aggregate root state published

getItemList: 
Array
(
    [0] => Array
        (
            [projectionId] => ec6331f0-306a-48bc-9ac3-c11114e55bbf
            [firstname] => Emmett
            [lastname] => Brown
        )

)
getProjectionIdForAggregateId 
ec6331f0-306a-48bc-9ac3-c11114e55bbf

getItem: 
Array
(
    [projectionId] => ec6331f0-306a-48bc-9ac3-c11114e55bbf
    [firstname] => Emmett
    [lastname] => Brown
)
getAggregateRootMappingsForProjectionId 
Array
(
    [0] => FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter Object
        (
            [projectionName:FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter:private] => account
            [projectionId:FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter:private] => ec6331f0-306a-48bc-9ac3-c11114e55bbf
            [aggregateName:FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter:private] => account
            [aggregateId:FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter:private] => 6a1e4b65-0810-4bb2-a120-6624be0a7107
            [externalId:FluxEco\Projection\Adapters\AggregateRoot\AggregateRootMappingAdapter:private] => 
        )

)
```

## Contributing :purple_heart:

Please ...

1. ... register an account at https://git.fluxlabs.ch
2. ... create pull requests :fire:

## Adjustment suggestions / bug reporting :feet:

Please ...

1. ... register an account at https://git.fluxlabs.ch
2. ... ask us for a Service Level Agreement: support@fluxlabs.ch :kissing_heart:
3. ... read and create issues
