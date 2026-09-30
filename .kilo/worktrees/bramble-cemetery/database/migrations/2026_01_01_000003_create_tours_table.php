<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_title')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('destination')->nullable();
            $table->string('category');
            $table->string('country')->default('বাংলাদেশ');
            $table->longText('description')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('days')->default(1);
            $table->integer('nights')->default(0);
            $table->string('departure_location')->nullable();
            $table->string('meeting_point')->nullable();
            $table->string('transport_type')->nullable();
            $table->decimal('price_per_person', 10, 2)->default(0);
            $table->integer('max_slots')->default(30);
            $table->integer('current_booked')->default(0);
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('important_info')->nullable();
            $table->json('faqs')->nullable();
            $table->json('features')->nullable();
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->integer('review_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();

            $table->foreign('destination_id')
                ->references('id')
                ->on('destinations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
