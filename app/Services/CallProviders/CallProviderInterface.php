<?php

namespace App\Services\CallProviders;

interface CallProviderInterface
{
    public function makeCall(string $phone): string;
}
