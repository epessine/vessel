<?php

namespace Vessel\Exceptions;

class CannotCallVesselDirectlyException extends \Exception
{
    public function __construct(string $componentName, string $methodName)
    {
        parent::__construct(
            "Cannot call [{$methodName}()] vessel method directly on component: {$componentName}"
        );
    }
}
