<?php


namespace FluxEco\Projection\Adapters\SchemaRegistry;


use RuntimeException;

class ProjectionSchemaRegistry
{
    protected static ?self $instance = null;
    private array $loadedDirectories = [];
    private array $aggregateProjections = [];
    private array $projections = [];

    private function __construct(array $projectionSchemaDirectories)
    {
        foreach ($projectionSchemaDirectories as $schemaDirectory) {
            $this->loadSchemaFiles($schemaDirectory);
        }
    }


    public static function new(array $projectionSchemaDirectories): self
    {
        if (static::$instance === null) {
            static::$instance = new self($projectionSchemaDirectories);
        }

        return static::$instance;
    }

    private function loadSchemaFiles(string $projectionSchemaDirectory): void
    {
        if ($this->directoryLoaded($projectionSchemaDirectory)) {
            return;
        }

        $filePaths = $this->getFilePaths($projectionSchemaDirectory);
        foreach ($filePaths as $filePath) {
            $this->setProjectionSchema($filePath);
        }
        $this->loadedDirectories[] = $projectionSchemaDirectory;
    }

    private function getFilePaths(string $schemaDirectory, string $schemaSuffix = 'yaml'): array
    {
        $filePaths = [];

        if (file_exists($schemaDirectory) === false || is_dir($schemaDirectory) === false) {
            throw new \RuntimeException('Direcotry no found: ' . $schemaDirectory);
        }

        $result = scandir($schemaDirectory);
        $directoryItems = array_diff($result, array('.', '..'));

        if (count($directoryItems) === 0) {
            return [];
        }


        foreach ($directoryItems as $directoryItem) {
            $filePath = $schemaDirectory . "/" . $directoryItem;
            if (is_file($filePath)) {
                if (pathinfo($filePath, PATHINFO_EXTENSION) !== $schemaSuffix) {
                    continue;
                }
                $filePaths[] = $filePath;
            }

            if (is_dir($filePath)) {

                echo $filePath;
                // Recursively call the function if directories found
                $filePathsSubdirectory = $this->getFilePaths($filePath);
                if (count($filePathsSubdirectory) > 0) {
                    array_push($filePaths, ...$filePathsSubdirectory);
                }
            }
        }

        if (count($filePaths) === 0) {
            throw new \RuntimeException('No Files found with suffix ' . $schemaSuffix);
        }

        return $filePaths;
    }

    private function setProjectionSchema(string $projectionSchemaFilePath): void
    {
        $projectionSchema = yaml_parse(file_get_contents($projectionSchemaFilePath));

        if(empty($projectionSchema['aggregateRootNames']) === false) {
            $aggregateRootNames = $projectionSchema['aggregateRootNames'];
            foreach($aggregateRootNames as $aggregateRootName) {
                $this->aggregateProjections[$aggregateRootName][] = $projectionSchema;
            }
        }
        $this->projections[$projectionSchema['name']] = $projectionSchema;
    }


    private function directoryLoaded(string $schemaDirectory): bool
    {
        return array_key_exists($schemaDirectory, $this->loadedDirectories);
    }

    final public function hasProjection(string $projectionName): bool
    {
        return !empty($this->projections[$projectionName]);
    }

    final public function hasAggregateProjections(string $aggregateName): bool
    {
        return !empty($this->aggregateProjections[$aggregateName]);
    }

    final public function getProjectionsForAggregate(string $aggregateName): array
    {
        if ($this->hasAggregateProjections($aggregateName) === false) {
            throw new RuntimeException('No schema found for aggregateName '.$aggregateName);
        }

        return $this->aggregateProjections[$aggregateName];
    }

    final public function getProjection(string $projectionName): array
    {
        if ($this->hasProjection($projectionName) === false) {
            throw new RuntimeException('No schema found for projection '.$projectionName. 'loaded projections: '.print_r($this->projections, true));
        }

        return $this->projections[$projectionName];
    }
}