<?php

namespace fluxProjection;

use FluxEco\Projection;

function reinitialize() {
    Projection\Api::newFromEnv()->reinitialize();
}