<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journalist_profile_publication', function (Blueprint $table) {
            $table->foreignId('journalist_profile_id')->constrained('journalist_profiles')->cascadeOnDelete();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();

            $table->primary(['journalist_profile_id', 'publication_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journalist_profile_publication');
    }
};
