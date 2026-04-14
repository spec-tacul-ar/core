<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('readmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id');
            $table->foreignId('project_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readmarks');
    }
};
