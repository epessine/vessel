<?php

namespace Vessel\Exceptions;

class BadVesselMethodException extends \Exception
{
    public function __construct(string $componentName, string $methodName)
    {
        parent::__construct(
            "Method [{$methodName}()] on component [{$componentName}] does not return a vessel instance."
        );
    }
}
