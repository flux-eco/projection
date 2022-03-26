<?php

namespace FluxEco\Projection\Core\Ports\ProjectionInitializer;
use FluxEco\Projection\Core\Application\Commands\CreateProjectionCommand;

interface CreateProjectionStorageHandler
{
    public function handle(CreateProjectionCommand $createProjectiontorageCommand);
}