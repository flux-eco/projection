<?php

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Ports};

class CreateProjectionStorageHandler implements Handler
{
    private Ports\Outbounds $outbounds;

    private function __construct(
        Ports\Outbounds $outbounds)
    {
        $this->outbounds = $outbounds;
    }

    public static function new(
        Ports\Outbounds $outbounds
    ): self
    {
        return new self(
            $outbounds
        );
    }


    final public function handle(Command|CreateProjectionStorageCommand $command)
    {
        $tableName = $command->getTableName();
        $schema = $command->getSchema();
        $this->outbounds->createProjectionStorage($tableName, $schema);
    }

}