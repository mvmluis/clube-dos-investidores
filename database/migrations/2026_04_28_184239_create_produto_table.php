<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto', function (Blueprint $table) {
            $table->id('ref');

            $table->string('titulo');
            $table->text('descricao');
            $table->decimal('valor', 12, 2);
            $table->boolean('ativo')->default(true);

            $table->date('data')->nullable();

            $table->string('imagem_principal')->nullable();

            $table->string('id_tipo');

            $table->string('localizacao');
            $table->string('valor_negociavel')->nullable();
            $table->text('areas_actividade')->nullable();

            $table->boolean('publicado')->default(false);
            $table->boolean('pedir_publicacao')->default(false);
            $table->boolean('is_saved')->default(false);
            $table->boolean('is_favorite')->default(false);

            $table->timestamps();

            $table->index('id_tipo');
            $table->index('publicado');
            $table->index('pedir_publicacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto');
    }
};
