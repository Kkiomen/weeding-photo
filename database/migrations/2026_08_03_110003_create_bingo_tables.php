<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingo_fields', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('⭐');
            $table->timestamps();
        });

        Schema::create('bingo_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->json('field_ids'); // 25 IDs, kolejność = pozycje 0-24
            $table->unsignedInteger('lines_won')->default(0); // 0..12 (5 rzędów + 5 kolumn + 2 przekątne)
            $table->boolean('full_card_won')->default(false);
            $table->timestamps();
        });

        Schema::create('bingo_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('bingo_cards')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('bingo_fields')->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained('photos')->nullOnDelete();
            $table->timestamp('marked_at');
            $table->timestamps();

            $table->unique(['card_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingo_marks');
        Schema::dropIfExists('bingo_cards');
        Schema::dropIfExists('bingo_fields');
    }
};
