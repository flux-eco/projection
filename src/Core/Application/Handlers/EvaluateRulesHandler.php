<?php

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Domain, Ports};

class EvaluateRulesHandler implements Handler
{
    private Ports\Outbounds $outbounds;

    private function __construct(
        Ports\Outbounds $outbounds
    )
    {
        $this->outbounds = $outbounds;
    }

    public static function new(
        Ports\Outbounds $outbounds
    ): self
    {
        return new self($outbounds);
    }

    public function handle(EvaluateRulesCommand|Command $command): bool
    {
        $schema = $command->getSchema();
        if(key_exists('rules', $schema) === false) {
            return true;
        }

        $rules =  $schema['rules'];
        $data =  $command->getData();
        foreach($rules as $aggregateName => $rule) {
            //todo multiple aggregate per projection
            //todo commandHandlerQueue for handling different rule types

            switch($rule['rule']) {
                case Domain\Models\RuleEnum::EQUALS:
                    $propertyValue = $data[$rule['property']];
                    if($propertyValue !== $rule['value']) {
                        return false;
                    }
                    break;
            }

            return true;
        }
    }
}