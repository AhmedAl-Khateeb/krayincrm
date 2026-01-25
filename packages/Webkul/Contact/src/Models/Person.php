<?php

namespace Webkul\Contact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Activity\Traits\LogsActivity;
use Webkul\Attribute\Models\AttributeValueProxy;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Contact\Contracts\Person as PersonContract;
use Webkul\Contact\Database\Factories\PersonFactory;
use Webkul\Lead\Models\LeadProxy;
use Webkul\Tag\Models\TagProxy;
use Webkul\User\Models\UserProxy;

class Person extends Model implements PersonContract
{
    use CustomAttribute;
    use HasFactory;
    use LogsActivity;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'persons';

    /**
     * Eager loading.
     *
     * @var string
     */
    protected $with = ['organization'];

    /**
     * The attributes that are castable.
     *
     * @var array
     */
    protected $casts = [
        'emails' => 'array',
        'contact_numbers' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'emails',
        'contact_numbers',
        'job_title',
        'user_id',
        'organization_id',
        'unique_id',
    ];

    protected $appends = ['attributeValues'];

    public function getAttributeValuesAttribute()
    {
        return $this->attributeValues()->get();
    }

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the organization that owns the person.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationProxy::modelClass());
    }

    /**
     * Get the activities.
     */
    public function attributeValues()
    {
        return $this->hasMany(AttributeValueProxy::modelClass(), 'entity_id', 'id')
            ->where('entity_type', 'persons');
    }

    // (اختياري) خلي القديم alias عشان أي كود تاني عندك ما يقعش
    public function attribute_values()
    {
        return $this->attributeValues();
    }

    /**
     * The tags that belong to the person.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'person_tags');
    }

    /**
     * Get the leads for the person.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(LeadProxy::modelClass(), 'person_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
