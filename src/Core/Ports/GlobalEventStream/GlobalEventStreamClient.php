<?php

namespace FluxEco\Projection\Core\Ports\GlobalEventStream;

interface GlobalStreamStorageClient
{
    public function loadCurrentStates(?array $subjectNames);
}