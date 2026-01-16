<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Lead\Contracts\Type as TypeContract;
use Webkul\Product\Models\ProductProxy; // لو موجود Proxy استخدمه، لو مش موجود هنستخدم Product مباشرة
use Webkul\Product\Models\Product;      // fallback

class Type extends Model implements TypeContract
{
    protected $table = 'lead_types';

    protected $fillable = [
        'name',
    ];

    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass());
    }

    /**
     * ✅ Products linked to this Lead Type
     */
    public function products(): BelongsToMany
    {
        // لو عندك ProductProxy استخدمه بدل Product
        $productModel = class_exists(ProductProxy::class) ? ProductProxy::modelClass() : Product::class;

        return $this->belongsToMany(
            $productModel,
            'lead_type_products',
            'lead_type_id',
            'product_id'
        );
    }
}
