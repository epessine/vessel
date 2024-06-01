<?php

use Vessel\VesselManager;

use function Pest\Laravel\get;

test('should set context id from header', function (): void {
    get('/context', [VesselManager::CONTEXT_HEADER => 'test'])->assertContent('test');
});
