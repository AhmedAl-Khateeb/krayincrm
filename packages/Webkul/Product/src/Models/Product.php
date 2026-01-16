<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Activity\Traits\LogsActivity;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Product\Contracts\Product as ProductContract;
use Webkul\Tag\Models\TagProxy;
use Webkul\Warehouse\Models\LocationProxy;
use Webkul\Warehouse\Models\WarehouseProxy;

// ✅ هنضيف TypeProxy/Type fallback
use Webkul\Lead\Models\TypeProxy;
use Webkul\Lead\Models\Type;

class Product extends Model implements ProductContract
{
    use CustomAttribute, LogsActivity;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'quantity',
        'price',
    ];

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseProxy::modelClass(), 'product_inventories');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(LocationProxy::modelClass(), 'product_inventories', 'product_id', 'warehouse_location_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventoryProxy::modelClass());
    }

    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'product_tags');
    }

    public function activities()
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'product_activities');
    }

    /**
     * ✅ Lead Types linked to this product
     */
    public function leadTypes(): BelongsToMany
    {
        $typeModel = class_exists(TypeProxy::class) ? TypeProxy::modelClass() : Type::class;

        return $this->belongsToMany(
            $typeModel,
            'lead_type_products',
            'product_id',
            'lead_type_id'
        );
    }
}
