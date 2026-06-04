<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('socialite_provider')->nullable()->after('password');
            $table->string('socialite_provider_id')->nullable()->after('socialite_provider');

            $table->unique(['socialite_provider', 'socialite_provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['socialite_provider', 'socialite_provider_id']);

            $table->dropColumn(['socialite_provider', 'socialite_provider_id']);
        });
    }
};
