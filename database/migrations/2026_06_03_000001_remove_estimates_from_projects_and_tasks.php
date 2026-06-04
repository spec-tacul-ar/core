<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'hide_estimates')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('hide_estimates');
            });
        }

        if (Schema::hasColumn('tasks', 'estimate')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('estimate');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('projects', 'hide_estimates')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('hide_estimates')->default(false)->after('next_requirement_reference');
            });
        }

        if (! Schema::hasColumn('tasks', 'estimate')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->integer('estimate')->nullable()->unsigned()->after('name');
            });
        }
    }
};
