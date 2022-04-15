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

//getAggregateRootMappingsForProjectionData
$projectionName = 'account';
$projectionData = [
    "firstname" => "Emmett",
    "lastname" => "Brown",
];
$aggregateRootMappings = fluxProjection\getAggregateRootMappingsForProjectionData($projectionName, $projectionData);
echo 'getAggregateRootMappingsForProjectionData'.PHP_EOL.PHP_EOL;
print_r($aggregateRootMappings);

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

//getAggregateIdForProjectionId
echo 'getAggregateIdForProjectionId: '.PHP_EOL;
$aggregateName = 'account';

$aggregateId = fluxProjection\getAggregateIdForProjectionId($projectionName, $projectionId, $aggregateName);

echo $aggregateId.PHP_EOL.PHP_EOL;


//getAggregateIdsForProjectionId
echo 'getAggregateIdsForProjectionId: '.PHP_EOL;

$aggregateIds = fluxProjection\getAggregateIdsForProjectionId($projectionName, $projectionId);

print_r($aggregateIds).PHP_EOL.PHP_EOL;


//getItem
echo "getItem: ".PHP_EOL;

$item = fluxProjection\getItem($projectionName, $projectionId);

print_r($item).PHP_EOL.PHP_EOL;


//register ExternalId
echo "registerExternalId: ".PHP_EOL.PHP_EOL;

$aggregateName = 'account';
$projectionName = 'account';
$externalId = '123';

fluxProjection\registerExternalId($aggregateName, $projectionName, $externalId);


//get AggregateId for ExternalId
echo "getAggregateIdForExternalId: ".PHP_EOL;
$aggregateName = 'account';
$projectionName = 'account';
$externalId = '123';

$aggregateId = fluxProjection\getAggregateIdForExternalId($aggregateName, $projectionName, $externalId);

echo $aggregateId.PHP_EOL.PHP_EOL;

//get ProjectionId for ExternalId
echo "getProjectionIdForExternalId: ".PHP_EOL;
$aggregateName = 'account';
$projectionName = 'account';
$externalId = '123';

$projectionId = fluxProjection\getProjectionIdForExternalId($aggregateName, $projectionName, $externalId);

echo $projectionId.PHP_EOL.PHP_EOL;
```

output
```
projection storage initialized

getAggregateRootMappingsForProjectionData

Array
(
    [account] => Array
        (
            [firstname] => Emmett
            [lastname] => Brown
        )

)
aggregate root state published

getItemList: 
Array
(
    [0] => Array
        (
            [projectionId] => 20cdb2e1-b811-44b7-8f82-ce08d0338bb4
            [firstname] => Emmett
            [lastname] => Brown
        )
)
getProjectionIdForAggregateId 
6c6512a4-499a-427f-9070-b3e66bf35ab3

getAggregateIdForProjectionId: 
6c8a642b-b7a0-48af-aaae-21564c0fe609

getAggregateIdsForProjectionId: 
Array
(
    [account] => 6c8a642b-b7a0-48af-aaae-21564c0fe609
)
getItem: 
Array
(
    [projectionId] => 6c6512a4-499a-427f-9070-b3e66bf35ab3
    [firstname] => Emmett
    [lastname] => Brown
)
registerExternalId: 

getAggregateIdForExternalId: 
2e9d3ad5-b727-49bf-9a60-47acc802a298

getProjectionIdForExternalId: 
bc3a543e-d9c0-421f-bd21-3c5732ea8f29
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
