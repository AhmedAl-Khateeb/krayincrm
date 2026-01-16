<?php

namespace App\Console\Commands;

use App\Notifications\NextCallDueNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\User\Models\User;

class SendNextCallNotifications extends Command
{
    protected $signature = 'crm:next-call-notify';
    protected $description = 'Notify users when next_call_at is due for organizations';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        // هات attribute_id بتاع next_call_at
        $nextCallAttrId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'next_call_at')
            ->value('id');

        if (!$nextCallAttrId) {
            $this->warn('next_call_at attribute not found.');

            return self::SUCCESS;
        }

        // هات كل organizations اللي next_call_at بتاعها <= اليوم
        $orgs = DB::table('attribute_values as av')
            ->join('organizations as o', 'o.id', '=', 'av.entity_id')
            ->where('av.entity_type', 'organizations')
            ->where('av.attribute_id', $nextCallAttrId)
            ->whereNotNull('av.date_value')
            ->where('av.date_value', '<=', $today)
            ->select('o.id', 'o.name', 'o.user_id', 'av.date_value')
            ->get();

        foreach ($orgs as $org) {
            if (!$org->user_id) {
                continue;
            }

            $user = User::find($org->user_id);
            if (!$user) {
                continue;
            }

            // منع تكرار نفس الاشعار في نفس اليوم
            $exists = $user->notifications()
                ->whereDate('created_at', $today)
                ->where('data->entity_type', 'organization')
                ->where('data->entity_id', $org->id)
                ->exists();

            if (!$exists) {
                $user->notify(new NextCallDueNotification(
                    entityType: 'organization',
                    entityId: (int) $org->id,
                    entityName: $org->name,
                    callDate: $org->date_value
                ));
            }
        }

        $this->info('Done');

        return self::SUCCESS;
    }
}
