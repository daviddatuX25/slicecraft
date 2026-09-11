<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTE (change NOT in the worksheet instructions): The midterm requires
        // database-backed username login, so this additive migration extends
        // the existing worksheet users table without replacing it.
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable()->unique()->after('name');
        });

        DB::table('users')->whereNull('username')->orderBy('id')->get(['id'])->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'username' => 'user-'.$user->id,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('username');
        });
    }
};
