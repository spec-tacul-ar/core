<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('contributors', 'collaborations');

        Schema::table('collaborations', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('updated_at');
        });

        DB::table('readmarks')
            ->orderBy('updated_at')
            ->get()
            ->each(function (object $readmark): void {
                DB::table('collaborations')
                    ->where('account_id', $readmark->account_id)
                    ->where('project_id', $readmark->project_id)
                    ->update(['read_at' => $readmark->updated_at]);
            });

        Schema::dropIfExists('readmarks');
    }

    public function down(): void
    {
        Schema::create('readmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id');
            $table->foreignId('project_id');
            $table->timestamps();
        });

        DB::table('collaborations')
            ->whereNotNull('read_at')
            ->orderBy('read_at')
            ->get()
            ->each(function (object $collaboration): void {
                DB::table('readmarks')->insert([
                    'account_id' => $collaboration->account_id,
                    'project_id' => $collaboration->project_id,
                    'created_at' => $collaboration->read_at,
                    'updated_at' => $collaboration->read_at,
                ]);
            });

        Schema::table('collaborations', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });

        Schema::rename('collaborations', 'contributors');
    }
};
