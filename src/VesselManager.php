<?php

namespace Vessel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VesselManager
{
    public const CONTEXT_HEADER = 'X-Vessel-Context-Id';

    private static string $contextId;

    public static function getContextId(): string
    {
        if (isset(self::$contextId)) {
            return self::$contextId;
        }

        /** @var Request $request */
        $request = request();
        /** @var string $contextId */
        $contextId = $request->header(self::CONTEXT_HEADER, Str::random(20));

        return self::$contextId = $contextId;
    }
}
