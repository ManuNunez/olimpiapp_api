<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('full_name');
            $table->date('date_of_birth')->nullable()->after('full_name');
            $table->string('curp')->nullable()->after('date_of_birth');
            $table->unsignedTinyInteger('status')->default(2)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'username',
                'status',
                'date_of_birth',
                'curp',
            ]);
        });
    }
};
