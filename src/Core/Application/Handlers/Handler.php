<?php


declare(strict_types=1);

namespace FluxEco\Projection\Core\Application\Handlers;

interface Handler
{
    public function handle(Command $command);
}