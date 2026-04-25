<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('next_requirement_reference')->unsigned()->default(1);
            $table->timestamps();
            $table->revisionable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
