<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La papelera de tiendas.
     *
     * Es una columna propia y no el `SoftDeletes` de Laravel: ese trait
     * escondería la tienda en todas las consultas, también en `$user->store`,
     * y el dueño tiene que poder seguir entrando a su panel mientras la tienda
     * está en la papelera. Lo único que la esconde es el catálogo público.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->timestamp('trashed_at')->nullable()->after('active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('trashed_at');
        });
    }
};
