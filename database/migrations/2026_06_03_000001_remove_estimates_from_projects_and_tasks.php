<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('hide_estimates');
        });

        DB::table('tasks')
            ->whereNotNull('estimate')
            ->select(['id', 'name', 'estimate'])
            ->chunkById(100, function ($tasks) {
                foreach ($tasks as $task) {
                    $hours = rtrim(rtrim(number_format($task->estimate * 0.25, 2, '.', ''), '0'), '.');
                    $suffix = ' (Estimate: ' . $hours . ' ' . ($hours === '1' ? 'hour' : 'hours') . ')';

                    DB::table('tasks')
                        ->where('id', $task->id)
                        ->update([
                            'name' => mb_substr($task->name, 0, max(0, 250 - mb_strlen($suffix))) . $suffix,
                        ]);
                }
            });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('estimate');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('hide_estimates')->default(false)->after('next_requirement_reference');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('estimate')->nullable()->unsigned()->after('name');
        });
    }
};
