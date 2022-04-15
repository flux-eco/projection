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

