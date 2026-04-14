<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained();
            $table->integer('reference')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('source')->nullable();
            $table->tinyInteger('weight')->unsigned()->nullable();
            $table->string('blocked_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->binary('history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
