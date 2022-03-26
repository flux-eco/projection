<?php


namespace FluxEco\Projection\Core\Ports\ValueObjectProvider;

interface ValueObjectProviderClient
{
    public function createUuid(): string;
}