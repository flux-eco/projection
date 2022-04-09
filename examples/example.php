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

