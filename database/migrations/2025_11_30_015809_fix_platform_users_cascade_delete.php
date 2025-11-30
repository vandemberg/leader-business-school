<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Corrige o problema onde deletar um PlatformUser estava deletando o User.
     * 
     * O problema: O onDelete('cascade') na foreign key user_id está correto para deletar
     * PlatformUser quando User é deletado, mas pode haver comportamento inesperado em alguns
     * bancos de dados ou versões do Laravel.
     * 
     * Solução: Vamos recriar a foreign key explicitamente para garantir que o comportamento
     * está correto. O cascade deve funcionar apenas quando User é deletado, não quando
     * PlatformUser é deletado.
     */
    public function up(): void
    {
        Schema::table('platform_users', function (Blueprint $table) {
            // Remove a foreign key antiga
            $table->dropForeign(['user_id']);
        });
        
        // Recria a foreign key com a configuração explícita
        Schema::table('platform_users', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_users', function (Blueprint $table) {
            // Remove a foreign key com restrict
            $table->dropForeign(['user_id']);
            
            // Restaura a foreign key original com cascade
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
