<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('project_id')->constrained();
            $table->string('email')->index();
            $table->string('role');
            $table->timestamps();

            $table->unique(['email', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
