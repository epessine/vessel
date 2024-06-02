<?php

namespace Vessel\Support;

use Illuminate\Support\Str;
use Vessel\VesselManager;

class PropertyHelper
{
    public static function updatedEventName(string $name): string
    {
        return Str::of($name)
            ->prepend(
                'vessel-',
                VesselManager::getContextId(),
                '-',
            )
            ->append('-updated')
            ->toString();
    }
}
