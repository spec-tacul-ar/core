<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unknowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
            $table->binary('history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unknowns');
    }
};
