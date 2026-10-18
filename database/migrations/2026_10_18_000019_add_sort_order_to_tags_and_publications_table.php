<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The drag-to-reorder contract (ADMIN_PANEL.md §3) covers all four registry
 * screens, so `tags` and `publications` gain the `sort_order` column that
 * `categories` and `topics` already carry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
