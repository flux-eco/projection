<?php

namespace fluxProjection;

use FluxEco\Projection;

function initialize() {
    Projection\Api::newFromEnv()->initialize();
}