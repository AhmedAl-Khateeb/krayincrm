<?php

namespace Webkul\Activity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Activity\Contracts\Activity as ActivityContract;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Lead\Models\LeadProxy;
use Webkul\Product\Models\ProductProxy;
use Webkul\User\Models\UserProxy;
use Webkul\Warehouse\Models\WarehouseProxy;

class Activity extends Model implements ActivityContract
{
    protected $table = 'activities';

    protected $with = ['user'];

    protected $casts = [
        'schedule_from' => 'datetime',
        'schedule_to'   => 'datetime',
    ];

    protected $fillable = [
        'title',
        'type',
        'location',
        'comment',
        'additional',
        'schedule_from',
        'schedule_to',
        'is_done',
        'user_id',
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * ✅ The leads that belong to the activity.
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(LeadProxy::modelClass(), 'lead_activities');
    }

    /**
     * The participants that belong to the activity.
     */
    public function participants()
    {
        return $this->hasMany(ParticipantProxy::modelClass());
    }

    /**
     * Get the file associated with the activity.
     */
    public function files()
    {
        return $this->hasMany(FileProxy::modelClass(), 'activity_id');
    }

    /**
     * The Person that belong to the activity.
     */
    public function persons()
    {
        return $this->belongsToMany(PersonProxy::modelClass(), 'person_activities');
    }

    /**
     * The products that belong to the activity.
     */
    public function products()
    {
        return $this->belongsToMany(ProductProxy::modelClass(), 'product_activities');
    }

    /**
     * The Warehouse that belong to the activity.
     */
    public function warehouses()
    {
        return $this->belongsToMany(WarehouseProxy::modelClass(), 'warehouse_activities');
    }
}

