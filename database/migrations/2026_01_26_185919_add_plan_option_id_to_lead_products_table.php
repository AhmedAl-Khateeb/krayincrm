<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lead_products', function (Blueprint $table) {
            // لو العمود مش موجود ضيفه
            if (!Schema::hasColumn('lead_products', 'plan_option_id')) {
                $table->unsignedInteger('plan_option_id')->nullable()->after('product_id');
            }
        });

        // ضيف FK لو مش موجود (Laravel مفيهوش check جاهز للـ FK فهنعمل try)
        try {
            Schema::table('lead_products', function (Blueprint $table) {
                $table->foreign('plan_option_id')
                    ->references('id')->on('attribute_options')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // غالباً FK موجود بالفعل، تجاهل
        }
    }

    public function down(): void
    {
        try {
            Schema::table('lead_products', function (Blueprint $table) {
                $table->dropForeign(['plan_option_id']);
            });
        } catch (\Throwable $e) {}

        if (Schema::hasColumn('lead_products', 'plan_option_id')) {
            Schema::table('lead_products', function (Blueprint $table) {
                $table->dropColumn('plan_option_id');
            });
        }
    }
};
