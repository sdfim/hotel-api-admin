<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Models\User')
            ->update(['tokenable_type' => 'App\Models\Channel']);
    }

    public function down(): void
    {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Models\Channel')
            ->update(['tokenable_type' => 'App\Models\User']);
    }
};
