<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('weight')->unsigned()->nullable();
            $table->timestamps();
            $table->revisionable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
