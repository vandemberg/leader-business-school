<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_personal')->default(false)->after('platform_id');
            $table->uuid('share_token')->nullable()->unique()->after('is_personal');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['is_personal', 'share_token']);
        });
    }
};
