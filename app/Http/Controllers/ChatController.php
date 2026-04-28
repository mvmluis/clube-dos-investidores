<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    private function createCustomDatabaseConnection($config): \Illuminate\Database\Connection
    {
        $connectionName = $config;

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

        return DB::connection($connectionName);
    }

    public function iniciar(Request $request)
    {
        $product_ref_to_update = $request->input('produto_ref');
        $conf = $request->input('conf');
        $tabela = $request->input('tabela');

        // Conectar ao banco de dados para produtos
        $connection = $this->createCustomDatabaseConnection($conf);

        // Atualizar o campo 'pedir_publicacao' para 0 na tabela de produtos
        $updated = $connection->table('produto')
            ->where('ref', $product_ref_to_update)
            ->update(['pedir_publicacao' => 0]);

        // Verificar se a atualização foi bem-sucedida
        if ($updated) {
            // Redirecionar para a página de exibição do produto com uma mensagem de sucesso
            return redirect()->route('chat.show', [
                'conf' => $conf,
                'tabela' => $tabela,
                'product_ref' => $product_ref_to_update,
            ])->with('success', 'Produto marcado como não publicado.');
        } else {
            // Se a atualização falhar, redirecionar com uma mensagem de erro
            return redirect()->route('chat.show', [
                'conf' => $conf,
                'tabela' => $tabela,
                'product_ref' => $product_ref_to_update,
            ])->with('error', 'Falha ao atualizar o produto.');
        }
    }


    public function show(Request $request, $conf, $tabela, $product_ref)
    {
        // Conectar ao banco de dados para usuários
        $customConnection2 = $this->createCustomDatabaseConnection('consultingcast3');
        $users = $customConnection2->select("SELECT id, name FROM users");
        $usersMap = collect($users)->keyBy('id');

        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Conectar ao banco de dados para o produto
        $connection = $this->createCustomDatabaseConnection($conf);

        // Verificar se o formulário de não publicação foi enviado
        if ($request->isMethod('post') && $request->input('produto_ref')) {
            $product_ref_to_update = $request->input('produto_ref');
            $connection->table($tabela)
                ->where('ref', $product_ref_to_update)
                ->update(['pedir_publicacao' => 0]);

            return redirect()->route('chat.show', [
                'conf' => $conf,
                'tabela' => $tabela,
                'product_ref' => $product_ref_to_update,
            ])->with('success', 'Produto marcado como não publicado.');
        }

        $tipoSelecionado = $request->input('tipo_produto');

        $query = $connection->table($tabela);
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }
        $pendingAdsCount = $connection->table($tabela)
            ->where('pedir_publicacao', true)
            ->count();

        // Filtrar produtos relacionados ao usuário
        $produtos = $query->where('ref', $product_ref)->get();

        $customConnection = $this->createCustomDatabaseConnection($conf);
        $userConnection = $this->createCustomDatabaseConnection('produtos');

        $produtosComDocumentos = [];
        $produtosComImagens = [];

        foreach ($produtos as $produto) {
            $documentos = $customConnection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            $imagens = $customConnection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();

            $isFavorite = $userConnection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $produto->ref)
                ->exists();

            $produto->documentos = $documentos;
            $produto->imagens = $imagens;
            $produto->is_favorite = $isFavorite;

            $produtosComDocumentos[] = $produto;
        }

        // Buscar o título do produto específico
        $produtoEspecifico = $connection->table($tabela)
            ->where('ref', $product_ref)
            ->first();
        $tituloProduto = $produtoEspecifico ? $produtoEspecifico->titulo : 'Produto não encontrado';

        // Buscar os tipos de produtos
        $tipos = $connection->table('tipo_produto')->get();

        // Buscar um product_ref da tabela user_produto, se necessário
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Se o product_ref for null, você pode atribuir um valor padrão ou tratar a ausência
        if ($productRef === null) {
            $productRef = '0'; // Ou qualquer valor padrão que faça sentido para sua aplicação
        }

        // Buscar mensagens específicas para o usuário e o produto
        $messagesQuery = $connection->table('user_produto')
            ->where('product_ref', $product_ref)
            ->orderBy('created_at', 'asc');

        // Se o usuário não for o administrador, filtrar mensagens de "pedido de publicação"
        if ($userId != 1) {
            $messagesQuery->where('mensagem', '!=', 'Pedir publicação');
        }

        $messages = $messagesQuery->get()
            ->map(function ($message) use ($usersMap) {
                $message->created_at = Carbon::parse($message->created_at);
                $message->remetente_nome = $usersMap[$message->user_id]->name ?? 'Desconhecido';
                $message->mensagem = $message->mensagem ?? ''; // Garante que o campo 'mensagem' não seja nulo
                return $message;
            });

        // Marcar todas as mensagens como lidas
        $this->markMessagesAsRead($userId, $product_ref);

        // Definir uma variável para a mensagem de aviso
        $noMessagesMessage = $messages->isEmpty() ? 'Não há mensagens para este produto.' : null;

        return view('chat.show', [
            'conf' => $conf,
            'tabela' => $tabela,
            'tipoSelecionado' => $tipoSelecionado,
            'messages' => $messages,
            'product_ref' => $product_ref,
            'produtos' => $produtosComDocumentos,
            'tipos' => $tipos,
            'userRole' => $userRole,
            'tituloProduto' => $tituloProduto,
            'pendingAdsCount' => $pendingAdsCount,
            'newMessagesCount' => $this->getNewMessagesCount($userId),
            'productRef' => $productRef,
            'noMessagesMessage' => $noMessagesMessage, // Mensagem de aviso
        ]);
    }


// Novo método para contar novas mensagens
    private function getNewMessagesCount($userId)
    {
        $connection = $this->createCustomDatabaseConnection('produtos'); // Substitua pelo nome do banco de dados correto
        return $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('is_read', 0)
            ->count();
    }


    public function send(Request $request, $conf, $tabela, $product_ref)
    {
        // Valide a entrada
        $request->validate([
            'mensagem' => 'required|string|max:255',
        ]);

        // Obtenha o ID do usuário autenticado
        $userId = Auth::id();

        // Obtenha o ID do destinatário (admin)
        $adminId = $this->getUserIdByProductRef($product_ref);

        // Crie uma nova mensagem no banco de dados
        $this->createMessage($userId, $adminId, $product_ref, $request->input('mensagem'));

        // Redirecione de volta para a página do chat com uma mensagem de sucesso
        return redirect()->route('chat.show', ['conf' => $conf, 'tabela' => $tabela, 'product_ref' => $product_ref])
            ->with('success', 'Mensagem enviada com sucesso.');
    }


    private function createMessage($userId, $recipientId, $productRef, $message)
    {
        $connection = $this->createCustomDatabaseConnection('produtos'); // Substitua 'nome_do_banco' pelo nome real do banco

        $connection->table('user_produto')->insert([
            'user_id' => $userId,
            'recipient_id' => $recipientId,
            'product_ref' => $productRef,
            'mensagem' => $message,
        ]);
    }

    private function markMessagesAsRead($userId, $productRef)
    {
        $connection = $this->createCustomDatabaseConnection('produtos'); // Substitua pelo nome do banco de dados correto

        $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('product_ref', $productRef)
            ->update(['is_read' => 1]);
    }

    private function getUserIdByProductRef($produtoRef)
    {
        $connection = $this->createCustomDatabaseConnection('produtos');

        // Verifique se há um usuário associado ao product_ref
        $userId = $connection->table('user_produto')
            ->where('product_ref', $produtoRef)
            ->where('user_id', '<>', Auth::id()) // Verifique se não é o usuário autenticado
            ->value('user_id');

        // Se não encontrar um ID de usuário válido, defina o ID padrão para o administrador
        return $userId ?: 1; // Ajuste o ID padrão conforme necessário
    }

    public function selectChat(Request $request, $conf, $tabela, $product_ref = null)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        $connection = $this->createCustomDatabaseConnection($conf);
        $customConnection2 = $this->createCustomDatabaseConnection('consultingcast3');

        // Buscar todos os product_ref da tabela user_produto
        $productRefs = $connection->table('user_produto')
            ->pluck('product_ref')
            ->unique(); // Remover duplicatas

        if ($productRefs->isEmpty()) {
            // Retornar para a view com uma mensagem se não houver dados
            return view('chatselecao.layout.dashboard', [
                'conf' => $conf,
                'tabela' => $tabela,
                'productsWithTitles' => [], // Nenhum produto
                'users' => collect(), // Nenhum usuário
                'userId' => $userId,
                'userRole' => $userRole,
                'messages' => collect(), // Nenhuma mensagem
                'noDataMessage' => 'Não há dados disponíveis.'
            ]);
        }

        // Buscar IDs de usuários associados a cada product_ref
        $userIdsByProductRef = [];
        foreach ($productRefs as $productRef) {
            $userIdsByProductRef[$productRef] = $connection->table('user_produto')
                ->where('product_ref', $productRef)
                ->where('user_id', '<>', 1) // Excluir o usuário com ID 1
                ->pluck('user_id')
                ->unique(); // Remover duplicatas
        }

        $allUserIds = collect($userIdsByProductRef)->flatten()->unique();

        // Buscar todos os usuários associados aos IDs
        if ($allUserIds->isNotEmpty()) {
            $users = $customConnection2->select('SELECT id, name FROM users WHERE id IN (' . implode(',', $allUserIds->toArray()) . ')');
            $associatedUsers = collect($users);
        } else {
            $associatedUsers = collect(); // Nenhum usuário encontrado
        }

        // Buscar títulos dos produtos e contagem de mensagens não lidas
        $productsWithTitles = $connection->table('user_produto')
            ->join('produto', 'user_produto.product_ref', '=', 'produto.ref')
            ->select('user_produto.product_ref', 'produto.titulo', 'user_produto.user_id', 'user_produto.is_read')
            ->whereIn('user_produto.product_ref', $productRefs)
            ->distinct()
            ->get()
            ->groupBy('product_ref')
            ->map(function ($items) {
                return [
                    'titulo' => $items->first()->titulo,
                    'user_ids' => $items->pluck('user_id')->unique(),
                    'unread_count' => $items->where('is_read', false)->count(),
                ];
            });

        // Buscar mensagens para todos os product_ref
        $messages = $connection->table('user_produto')
            ->whereIn('product_ref', $productRefs)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($associatedUsers) {
                $message->created_at = Carbon::parse($message->created_at);
                $message->remetente_nome = $associatedUsers->firstWhere('id', $message->user_id)->name ?? 'Desconhecido';
                $message->mensagem = $message->mensagem ?? '';
                return $message;
            });

        return view('chatselecao.layout.dashboard', [
            'conf' => $conf,
            'tabela' => $tabela,
            'productsWithTitles' => $productsWithTitles, // Enviar títulos e contagens de mensagens para a view
            'users' => $associatedUsers,
            'userId' => $userId,
            'userRole' => $userRole,
            'messages' => $messages, // Enviar mensagens para a view
            'noDataMessage' => null // Nenhuma mensagem se não há dados
        ]);
    }

}
