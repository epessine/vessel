<?php

namespace Vessel\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Vessel\BaseVessel;
use Vessel\VesselManager;

class VesselHelper
{
    public static function cacheKey(string|BaseVessel $vessel): string
    {
        $class = is_string($vessel) ? $vessel : $vessel::class;

        if (! is_subclass_of($class, BaseVessel::class)) {
            throw new InvalidArgumentException("[$class] is not subclass of [Vessel\BaseVessel]");
        }

        return Str::of('vessel_')
            ->append($class, '_', VesselManager::getContextId())
            ->toString();
    }
}
