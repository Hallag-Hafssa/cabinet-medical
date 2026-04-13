<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicaments', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('dosage')->nullable();
            $table->string('forme')->nullable(); // comprimé, sirop, injection, etc.
            $table->timestamps();
        });

        Schema::create('ordonnance_medicament', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordonnance_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicament_id')->constrained()->onDelete('cascade');
            $table->string('posologie'); // ex: "1 comprimé 3 fois par jour"
            $table->string('duree');     // ex: "7 jours"
            $table->text('remarques')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnance_medicament');
        Schema::dropIfExists('medicaments');
    }
};
