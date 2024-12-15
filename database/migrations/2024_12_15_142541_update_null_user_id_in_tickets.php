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
    public function up()
    {
        // Actualiza los registros de tickets con un user_id válido
        DB::table('tickets')->whereNull('user_id')->update([
            'user_id' => 1, // Cambia 1 por el ID válido de un usuario en tu base de datos
        ]);

        // Asegurarse de que la columna 'user_id' no permita valores nulos
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // Comprobar si la clave foránea ya existe y eliminarla si es necesario
        if (Schema::hasTable('tickets')) {
            DB::statement('
                ALTER TABLE tickets
                DROP CONSTRAINT IF EXISTS tickets_user_id_foreign;
            ');

            // Agregar la relación de clave foránea
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Eliminar la clave foránea
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Si lo necesitas, puedes volver a hacer nullable la columna
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
