<?php

namespace App\Services\CallProviders;

class CiscoJabberCallProvider implements CallProviderInterface
{
    public function makeCall(string $phone): string
    {
        // يشيل المسافات والرموز ويخلي الرقم digits بس
        $digits = preg_replace('/\D+/', '', $phone);

        // لو الرقم UAE
        if (str_starts_with($digits, '00971')) {
            $digits = '971' . substr($digits, 5);
        }

        if (str_starts_with($digits, '971')) {
            $normalized = '+' . $digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalized = '+971' . substr($digits, 1);
        } else {
            $normalized = '+971' . $digits;
        }

        return 'jabber://' . $normalized;
    }
}
