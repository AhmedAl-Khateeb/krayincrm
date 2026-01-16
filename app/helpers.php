<?php

use App\Services\CallProviders\CallProviderInterface;

if (! function_exists('call_link')) {
    function call_link(string $phone): string
    {
        return app(CallProviderInterface::class)->makeCall($phone);
    }
}
