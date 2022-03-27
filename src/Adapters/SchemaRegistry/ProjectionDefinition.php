<?php


namespace FluxEco\Projection\Adapters\SchemaRegistry;

use FluxEco\Projection\Core\{Ports, Domain};

class ProjectionDefinition implements Ports\SchemaRegistry\ProjectionDefinition
{
    const ARRAY_KEY_NAME_AGGREGATE_ROOT_REF = 'index';
    private string $projectionName;
    private array $subjectNames;
    private string $schemaFilePath;

    private function __construct(
        string $projectionName,
        string $schemaYamlFilePath
    )
    {
        $this->projectionName = $projectionName;
        $this->schemaFilePath = $schemaYamlFilePath;


        $schema = yaml_parse(file_get_contents($schemaYamlFilePath));
        $properties = $schema['properties'];

        $this->subjectNames = $this->extractCorrespondingSubjectNames($properties);
    }

    public static function fromSchemaYamlFilePath(string $schemaYamlFilePath): self
    {
        $projectionName = pathinfo($schemaYamlFilePath, PATHINFO_FILENAME);

        return new self($projectionName, $schemaYamlFilePath);
    }


    private function extractCorrespondingSubjectNames(array $properties): array
    {
        $correspondingSubjectNames = [];
        foreach ($properties as $propertyName => $propertyValue) {

            if(array_key_exists(self::ARRAY_KEY_NAME_AGGREGATE_ROOT_REF, $propertyValue)) {
                $dottedKey = $propertyValue[self::ARRAY_KEY_NAME_AGGREGATE_ROOT_REF];
                $arrayKey = explode('.', $dottedKey);
                $subjectName = $arrayKey[0];
                $correspondingSubjectNames[] = $subjectName;
            }

        }
        return $correspondingSubjectNames;
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getSubjectNames(): array
    {
        return $this->subjectNames;
    }

    final public function getSchemaFilePath(): string
    {
        return $this->schemaFilePath;
    }


}