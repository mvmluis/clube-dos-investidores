<?php

namespace App\Http\Controllers;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Utilizadores extends Controller
{
    function createCustomDatabaseConnection($config): \Illuminate\Database\Connection
    {
        // Gere um nome de conexão exclusivo com base no nome do banco de dados
        $connectionName = $config;

        // Verifique se a configuração para a conexão já existe
        if (!Config::has("database.connections.$connectionName")) {
            Config::set("database.connections.$connectionName", [
                'driver' => 'mysql',
                'host' => '10.1.55.10',
                'database' => $config,
                'username' => 'luis',
                'password' => 'Admin1234',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        }

        // Retorne a conexão usando o nome gerado
        return DB::connection($connectionName);
    }

    public function utilizadores($conf, $tabela)
    {
        // Cria a conexão personalizada
        $customConnection = $this->createCustomDatabaseConnection('consultingcast3');

        // Obtém todos os usuários, exceto os que têm o papel de 'manager'
        $users = $customConnection->table('users')->where('role', '!=', 'manager')->get();

        // Obtém o usuário autenticado
        $loggedInUserId = Auth::id(); // Obtém o ID do usuário autenticado

        $data = [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => str_replace('_', '', $conf),
            'conf' => $conf,
            'tabela' => $tabela,
            'users' => $users,
            'loggedInUserId' => $loggedInUserId, // Passa o ID do usuário autenticado para a view
        ];

        // Retorna a view com os dados atualizados
        return view('utilizadores.layout.dashboard', $data);
    }

    public function utilizadoresavaliacao($conf, $tabela)
    {
        $customConnection = $this->createCustomDatabaseConnection('consultingcast3');

        // Obtém todos os usuários propostos que ainda estão em avaliação (não aceitos nem rejeitados)
        $proposedUsers = $customConnection->table('utilizadores_propostos')
            ->leftJoin('users', 'utilizadores_propostos.user_id', '=', 'users.id')
            ->select('utilizadores_propostos.*', 'users.name as sugerido_por_nome', 'users.email as sugerido_por_email', 'users.contacto as sugerido_por_contacto')
            ->where('utilizadores_propostos.estado', 'em_avaliacao') // Filtra apenas os usuários em avaliação
            ->get();

        $data = [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => str_replace('_', '', $conf),
            'conf' => $conf,
            'tabela' => $tabela,
            'proposedUsers' => $proposedUsers, // Adiciona os usuários propostos
        ];

        // Retornar a view com os dados atualizados
        return view('utilizadorespropostos.layout.dashboard', $data);
    }


    public function store(Request $request)
    {
        // Validação dos dados do formulário
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:utilizadores_propostos,email',
            'contacto' => 'nullable|string|max:20',
        ]);

        // Criar a conexão personalizada
        $connection = $this->createCustomDatabaseConnection('consultingcast3');

        // Obter o ID do usuário autenticado
        $userId = Auth::id(); // Obtém o ID do usuário autenticado

        // Inserir os dados na tabela `utilizadores_propostos`
        $connection->table('utilizadores_propostos')->insert([
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'contacto' => $request->input('contacto'),
            'user_id' => $userId, // Usa o ID do usuário autenticado
        ]);

        $conf = 'produtos'; // Substitua pelo valor real ou pela lógica para obter o valor
        $tabela = 'produto';

        return redirect()->route('utilizadores', ['conf' => $conf, 'tabela' => $tabela])
            ->with('success', 'Utilizador adicionado com sucesso!');
    }

    public function updateEstados(Request $request)
    {
        // Obtenha os dados da requisição
        $data = $request->input('estado');

        // Crie uma conexão personalizada
        $connection = $this->createCustomDatabaseConnection('consultingcast3');

        // Use a conexão personalizada para atualizar os estados
        foreach ($data as $id => $estado) {
            if ($estado === 'aceite') {
                // Obtenha o utilizador proposto
                $proposedUser = $connection->table('utilizadores_propostos')->where('id', $id)->first();

                if ($proposedUser) {
                    // Defina uma senha simples e criptografe-a
                    $simplePassword = 'password123'; // Senha simples
                    $hashedPassword = Hash::make($simplePassword); // Criptografa a senha

                    // Crie o novo utilizador na tabela 'users'
                    $connection->table('users')->insert([
                        'name' => $proposedUser->nome,
                        'email' => $proposedUser->email,
                        'contacto' => $proposedUser->contacto,
                        'password' => $hashedPassword, // Senha criptografada
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Enviar e-mail com a senha temporária
                    $this->sendWelcomeEmail($proposedUser->email, $simplePassword);
                }
            }

            // Atualize o estado do utilizador proposto
            $connection->table('utilizadores_propostos')->where('id', $id)->update(['estado' => $estado]);
        }

        $conf = 'produtos'; // Substitua pelo valor real ou pela lógica para obter o valor
        $tabela = 'produto';
        // Redirecione com uma mensagem de sucesso
        return redirect()->route('utilizadores.avaliacao', ['conf' => $conf, 'tabela' => $tabela])
            ->with('success', 'Estado atualizado com sucesso!');
    }

    private function sendWelcomeEmail($email, $password)
    {
        Mail::to($email)->send(new WelcomeMail($password));
    }

    public function showActiveUsers()
    {
        // Crie uma conexão personalizada
        $connection = $this->createCustomDatabaseConnection('consultingcast3');

        // Obtenha todos os utilizadores ativos
        $activeUsers = $connection->table('users')
            ->where('status', 'ativo')
            ->get();

        // Passe os dados para a view
        return view('utilizadoresativos.layout.dashboard', ['activeUsers' => $activeUsers]);
    }

}
