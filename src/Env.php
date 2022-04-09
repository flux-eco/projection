<?php

namespace FluxEco\Projection;

class Env
{
    const PROJECTION_STORAGE_CONFIG_ENV_PREFIX = 'PROJECTION_STORAGE_CONFIG_ENV_PREFIX';
    const PROJECTION_APP_SCHEMA_DIRECTORY = 'PROJECTION_APP_SCHEMA_DIRECTORY';
    const PROJECTION_ECO_SCHEMA_DIRECTORY = 'PROJECTION_ECO_SCHEMA_DIRECTORY';


    private function __construct()
    {

    }

    public static function new() : self
    {
        return new self();
    }

    public function getProjectionAppSchemaDirectory() : string
    {
        return getEnv(self::PROJECTION_APP_SCHEMA_DIRECTORY);
    }

    public function getProjectionEcoSchemaDirectory() : string
    {
        return getEnv(self::PROJECTION_ECO_SCHEMA_DIRECTORY);
    }

    public function getProjectionStorageConfigEnvPrefix() : string
    {
        return getEnv(self::PROJECTION_STORAGE_CONFIG_ENV_PREFIX);
    }
}