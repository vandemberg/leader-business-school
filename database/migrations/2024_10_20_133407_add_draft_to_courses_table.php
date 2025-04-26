<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'inprogress', 'decrepeated', 'complete'])
                ->default('draft')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'outdated', 'open', 'inprogress'])
                ->default('inprogress')
                ->change();
        });
    }
};
