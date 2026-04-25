<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained();
            $table->string('name');
            $table->integer('estimate')->nullable()->unsigned();
            $table->boolean('is_complete')->default(false);
            $table->tinyInteger('weight')->unsigned()->nullable();
            $table->timestamps();
            $table->revisionable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
