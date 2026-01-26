<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_type_products', function (Blueprint $table) {
            $table->increments('id');

            // IMPORTANT: match lead_types.id type (often INT unsigned)
            $table->unsignedInteger('lead_type_id');
            $table->unsignedInteger('product_id');

            $table->timestamps();

            $table->unique(['lead_type_id', 'product_id'], 'ltp_unique');

            $table->foreign('lead_type_id', 'ltp_lead_type_fk')
                ->references('id')->on('lead_types')
                ->onDelete('cascade');

            $table->foreign('product_id', 'ltp_product_fk')
                ->references('id')->on('products')
                ->onDelete('cascade');
            // $table->unsignedBigInteger('plan_option_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_type_products');
    }
};
