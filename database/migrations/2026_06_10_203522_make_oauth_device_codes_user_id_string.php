<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropIndex('oauth_device_codes_user_id_index');
        });

        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->string('user_id')->nullable()->change();
        });

        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropIndex('oauth_device_codes_user_id_index');
        });

        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->index('user_id');
        });
    }
};
