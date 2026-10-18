<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            $table->string('content_type')->default('news');
            $table->string('source_type')->default('internal');

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();

            $table->foreignId('featured_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained('publications')->nullOnDelete();

            $table->text('external_url')->nullable();
            $table->string('interviewee_name')->nullable();
            $table->string('interviewee_title')->nullable();
            $table->text('video_url')->nullable();
            $table->json('meta')->nullable();

            $table->boolean('is_featured')->default(false);

            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->foreignId('seo_og_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('canonical_url_override')->nullable();

            $table->timestamps();

            $table->index('content_type');
            $table->index('source_type');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
