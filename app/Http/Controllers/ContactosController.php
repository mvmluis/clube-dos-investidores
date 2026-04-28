<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactosController extends Controller
{
    function createCustomDatabaseConnection($config): \Illuminate\Database\Connection
    {
        // Gere um nome de conexão exclusivo com base no nome do banco de dados
        $connectionName = $config;

        // Verifique se a configuração para a conexão já existe
        // Verifique se a configuração para a conexão já existe
        if (!Config::has("database.connections.$connectionName")) {
            Config::set("database.connections.$connectionName", [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => $config,
                'username' => 'root',
                'password' => 'Consultingcast2026',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        }

        // Retorne a conexão usando o nome gerado
        return DB::connection($connectionName);
    }

    public function index($conf, $tabela)
    {
        // Cria uma conexão personalizada, se necessário
        $connection = $this->createCustomDatabaseConnection($conf);

        // Obtém os dados da tabela contactos, juntando com as tabelas users e produto
        $contactos = $connection->table('produtos.contactos AS c')
            ->join('consultingcast3.users AS u', 'c.user', '=', 'u.id') // Referência cruzada para a tabela users no banco de dados consultingcast3
            ->join('produtos.produto AS p', 'c.ref', '=', 'p.ref') // Referência cruzada para a tabela produto no banco de dados produtos
            ->select(
                'c.id', // Adiciona o id da tabela contactos
                'c.ref',
                'u.name as user_name',
                'c.data',
                'c.concluido',
                'p.titulo as produto_titulo'
            )
            ->get();

        // Converte a data para objetos Carbon
        foreach ($contactos as $contacto) {
            $contacto->data = $contacto->data ? \Carbon\Carbon::parse($contacto->data) : null;
        }

        // Retorna a view com os dados
        return view('contactos.layout.dashboard', compact('contactos', 'conf', 'tabela'));
    }


    public function update(Request $request, $id)
    {
        // Cria uma conexão personalizada, se necessário
        $connection = $this->createCustomDatabaseConnection('produtos');

        // Atualiza o status do contacto
        $connection->table('contactos')
            ->where('id', $id)
            ->update(['concluido' => $request->input('concluido')]);

        return redirect()->back()->with('success', 'Status atualizado com sucesso.');
    }
}
