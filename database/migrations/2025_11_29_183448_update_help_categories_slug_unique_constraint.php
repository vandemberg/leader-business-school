<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove a constraint única antiga do slug
        Schema::table('help_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Adiciona uma constraint única composta (slug, platform_id)
        // Isso permite que diferentes plataformas tenham categorias com o mesmo slug
        Schema::table('help_categories', function (Blueprint $table) {
            $table->unique(['slug', 'platform_id'], 'help_categories_slug_platform_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a constraint única composta
        Schema::table('help_categories', function (Blueprint $table) {
            $table->dropUnique('help_categories_slug_platform_unique');
        });

        // Restaura a constraint única simples do slug
        Schema::table('help_categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};
