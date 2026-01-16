<?php

namespace App\Services\CallProviders;

class CiscoTelCallProvider implements CallProviderInterface
{
    public function makeCall(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        return 'ciscotel://' . $phone;
    }
}
