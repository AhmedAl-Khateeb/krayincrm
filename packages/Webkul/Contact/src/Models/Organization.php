<?php

namespace Webkul\Contact\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Attribute\Models\AttributeValueProxy;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Contact\Contracts\Organization as OrganizationContract;
use Webkul\User\Models\UserProxy;

class Organization extends Model implements OrganizationContract
{
    use CustomAttribute;

    protected $casts = [
        'address' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'user_id',
    ];

    public function customAttributeValues()
    {
        return $this->hasMany(
            AttributeValueProxy::modelClass(),
            'entity_id'
        )->where('entity_type', 'organizations')
         ->with('attribute');
    }

    public function attributeValues()
{
    return $this->hasMany(AttributeValueProxy::modelClass(), 'entity_id')
        ->where('entity_type', 'organizations')
        ->with('attribute');
}

    /**
     * Alias for blade components.
     */
    public function attribute_values()
    {
        return $this->customAttributeValues();
    }

    /**
     * Get persons.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function persons()
    {
        return $this->hasMany(PersonProxy::modelClass());
    }

    /**
     * Get the user that owns the lead.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
