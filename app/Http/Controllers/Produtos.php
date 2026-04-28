<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Produtos extends Controller
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

    public function index(Request $request, $conf, $tabela)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Criar a conexão para a tabela de produtos
        $connection = $this->createCustomDatabaseConnection($conf);

        // Obter o tipo de produto selecionado
        $tipoSelecionado = $request->input('tipo_produto');

        // Construir a consulta para buscar produtos
        $query = $connection->table($tabela);

        // Aplicar o filtro se um tipo estiver selecionado
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        // Buscar produtos
        $produtos = $query->get();

        // Criar uma segunda conexão para a tabela de documentos, imagens e favoritos
        $customConnection = $this->createCustomDatabaseConnection($conf);
        $userConnection = $this->createCustomDatabaseConnection('produtos');

        // Inicializar arrays para armazenar documentos e imagens agrupados por produto
        $produtosComDocumentos = [];

        // Iterar sobre cada produto para buscar seus documentos, imagens e favoritos associados
        foreach ($produtos as $produto) {
            // Buscar documentos para o produto atual
            $documentos = $customConnection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            // Buscar imagens para o produto atual
            $imagens = $customConnection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();

            // Verificar se o produto é favorito para o usuário atual
            $isFavorite = $userConnection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $produto->ref)
                ->exists();

            // Adicionar documentos, imagens e estado de favorito ao produto atual
            $produto->documentos = $documentos;
            $produto->imagens = $imagens;
            $produto->is_favorite = $isFavorite;

            // Adicionar o produto com documentos, imagens e estado de favorito ao array
            $produtosComDocumentos[] = $produto;
        }

        // Verificar se o usuário é um administrador
        if ($userId == 1) {
            // Se o usuário for administrador, buscar todos os product_ref da tabela user_produto
            $productRefs = $connection->table('user_produto')
                ->pluck('product_ref')
                ->unique(); // Remover duplicatas, se houver
        } else {
            // Se o usuário não for administrador, buscar apenas os product_ref associados ao usuário
            $productRefs = $connection->table('user_produto')
                ->where('user_id', $userId)
                ->pluck('product_ref')
                ->unique(); // Remover duplicatas, se houver
        }

        // Buscar um product_ref da tabela user_produto
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Se o product_ref for null, você pode atribuir um valor padrão ou tratar a ausência
        if ($productRef === null) {
            $productRef = '0'; // Ou qualquer valor padrão que faça sentido para sua aplicação
        }

        $pendingAdsCount = $connection->table($tabela)
            ->where('pedir_publicacao', true)
            ->count();

        // Contar as notificações não lidas
        $messagesQuery = $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('is_read', false);

        // Excluir mensagens de "pedido de publicação" para usuários não administradores
        if ($userId != 1) {
            $messagesQuery->where('mensagem', '!=', 'Pedir publicação');
        }

        $newMessagesCount = $messagesQuery->count();

        // Buscar tipos de produto para o dropdown
        $tipos = $connection->table('tipo_produto')->get();

        $data = [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => str_replace('_', '', $conf),
            'conf' => $conf,
            'tabela' => $tabela,
            'produtos' => $produtosComDocumentos,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado, // Passar o tipo selecionado para a view
            'userId' => $userId,
            'userRole' => $userRole,
            'pendingAdsCount' => $pendingAdsCount,
            'productRef' => $productRef,
            'productRefs' => $productRefs, // Enviar todos os product_ref para a view
            'newMessagesCount' => $newMessagesCount, // Adicionar o número de novas mensagens
        ];

        // Retornar a view com os dados atualizados
        return view('novo_dashboard.layout.dashboard', $data);
    }

    public function pedidosPublicacao(Request $request, $conf, $tabela)
    {
        $userId = Auth::id(); // ID do usuário autenticado

        // Criar a conexão para a tabela de produtos e para a tabela de usuários
        $connection = $this->createCustomDatabaseConnection('consultingcast3');
        $secondconnection = $this->createCustomDatabaseConnection($conf);

        // Verificar se o usuário autenticado existe na tabela de usuários na conexão 'consultingcast3'
        $userExists = $connection->table('users')
            ->where('id', $userId)
            ->exists();

        if (!$userExists) {
            // Se o usuário não existir, você pode redirecionar ou exibir uma mensagem de erro
            return redirect()->route('home')->withErrors('Usuário não encontrado.');
        }

        $anuncios = $secondconnection->table($tabela)
            ->join('user_produto', 'user_produto.product_ref', '=', $tabela . '.ref') // Realiza o join usando ref e product_ref
            ->where('user_produto.user_id', $userId) // Filtra pelo usuário autenticado
            ->where($tabela . '.pedir_publicacao', false) // Filtra pelos produtos que pediram publicação
            ->select($tabela . '.*') // Seleciona todas as colunas da tabela de produtos
            ->distinct() // Garante que os resultados são distintos
            ->get()
            ->map(function ($anuncio) {
                // Converte a data para um objeto Carbon
                $anuncio->data = Carbon::parse($anuncio->data);
                return $anuncio;
            });

        // Retornar a view com os anúncios pedidos para publicação
        return view('anunciosparauser.layout.dashboard', compact('anuncios', 'conf', 'tabela'));
    }


    public function create(Request $request, $conf, $tabela)
    {
        // Conectar ao banco de dados
        $connection = $this->createCustomDatabaseConnection($conf);

        // Obter o ID e o papel do usuário autenticado
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Obter todos os tipos de produto
        $tipos = $connection->table('tipo_produto')->get();

        // Obter o tipo de produto selecionado da requisição
        $tipoSelecionado = $request->input('tipo_produto');

        // Construir a consulta para buscar produtos
        $query = $connection->table($tabela);

        // Aplicar o filtro de tipo, se selecionado
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        // Buscar produtos
        $produtos = $query->get();
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Se o product_ref for null, você pode atribuir um valor padrão ou tratar a ausência
        if ($productRef === null) {
            $productRef = '0'; // Ou qualquer valor padrão que faça sentido para sua aplicação
        }

        // Preparar dados para a view
        $data = [
            'conf' => $conf,
            'tabela' => $tabela,
            'userRole' => $userRole,
            'userId' => $userId,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado,
            'produtos' => $produtos,
            'productRef' => $productRef,
        ];

        // Retornar a view com os dados
        return view('produtos.create', $data);
    }

    public function edit(Request $request, $conf, $tabela, $id)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;
        $connection = $this->createCustomDatabaseConnection($conf);

        // Buscar o produto
        $produto = $connection->table($tabela)->where('ref', $id)->first();

        // Buscar documentos associados ao produto
        $documentos = $connection->table('documentos')->where('ref', $id)->get();

        // Buscar imagens associadas ao produto
        $imagens = $connection->table('produto_imagens')->where('produto_ref', $id)->get();

        // Buscar tipos de produto
        $tipos = $connection->table('tipo_produto')->get();
        $tipoSelecionado = $request->input('tipo_produto');

        // Filtrar produtos por tipo se selecionado
        $query = $connection->table($tabela);
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Dados a serem passados para a view
        $data = [
            'conf' => $conf,
            'tabela' => $tabela,
            'produto' => $produto,
            'documentos' => $documentos,
            'imagens' => $imagens, // Adicionar imagens aos dados
            'userRole' => $userRole,
            'userId' => $userId,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado,
            'productRef' => $productRef,
        ];

        return view('produtos.edit', $data);
    }


    public function editForm(Request $request, $conf, $tabela, $id)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;
        $connection = $this->createCustomDatabaseConnection($conf);

        // Buscar o produto
        $produto = $connection->table($tabela)->where('ref', $id)->first();

        // Buscar documentos associados ao produto
        $documentos = $connection->table('documentos')->where('ref', $id)->get();

        // Buscar imagens associadas ao produto
        $imagens = $connection->table('produto_imagens')->where('produto_ref', $id)->get();

        // Buscar tipos de produto
        $tipos = $connection->table('tipo_produto')->get();
        $tipoSelecionado = $request->input('tipo_produto');

        // Filtrar produtos por tipo se selecionado
        $query = $connection->table($tabela);
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Dados a serem passados para a view
        $data = [
            'conf' => $conf,
            'tabela' => $tabela,
            'produto' => $produto,
            'documentos' => $documentos,
            'imagens' => $imagens, // Adicionar imagens aos dados
            'userRole' => $userRole,
            'userId' => $userId,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado,
            'productRef' => $productRef,
        ];

        return view('anuncioseditaruser.edit', $data);
    }

    public function store(Request $request, $conf, $tabela)
    {
        $connection = $this->createCustomDatabaseConnection($conf);

        // Processamento e validação de dados
        $valor = $request->input('valor');
        $valorLimpo = str_replace(['€', '.', ' '], ['', '', ''], $valor);
        $valorLimpo = str_replace(',', '.', $valorLimpo);
        $request->merge(['valor' => $valorLimpo]);

        $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'valor' => 'required|numeric',
            'ativo' => 'required|boolean',
            'data' => 'nullable|date',
            'imagem_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'nullable|file|max:2048',
            'id_tipo' => 'required|string',
            'localizacao' => 'required|string',
            'valor_negociavel' => 'nullable|string',
            'areas_actividade' => 'nullable|string',
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Dados do produto
        $produtoData = $request->only(['titulo', 'descricao', 'valor', 'ativo', 'id_tipo', 'localizacao', 'valor_negociavel', 'areas_actividade']);
        $produtoData['data'] = $request->input('data');
        $produtoData['publicado'] = false; // Inicialmente não publicado
        $produtoData['pedir_publicacao'] = false; // Inicialmente não solicitado
        $produtoData['is_saved'] = 0; // Inicialmente não salvo

        $produtoId = $connection->table($tabela)->insertGetId($produtoData);

        // Manipulação de arquivos
        if ($request->hasFile('imagem_principal')) {
            $path = $request->file('imagem_principal')->store('public/imagens');
            $connection->table($tabela)->where('ref', $produtoId)->update(['imagem_principal' => str_replace('public/', '', $path)]);
        }

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('public/documentos');
                $connection->table('documentos')->insert([
                    'ref' => $produtoId,
                    'arquivo' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(),
                ]);
            }
        }

        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $file) {
                $path = $file->store('public/imagens');
                $connection->table('produto_imagens')->insert([
                    'produto_ref' => $produtoId,
                    'caminho' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Determinar a ação com base no input
        $action = $request->input('action');
        $userId = Auth::id(); // Obter o ID do usuário autenticado

        // Variáveis para a mensagem e a rota
        $message = '';
        $route = '';

        if ($action == 'publicar') {
            // Atualizar produto para publicado
            $connection->table($tabela)->where('ref', $produtoId)->update([
                'publicado' => true,
                'is_saved' => 0 // Assegura que is_saved não seja alterado ao publicar
            ]);
            $message = 'Produto publicado com sucesso.';
            $route = 'produtos';
        } elseif ($action == 'pedir_publicacao') {
            // Atualizar produto para pedido de publicação
            $connection->table($tabela)->where('ref', $produtoId)->update([
                'pedir_publicacao' => true,
                'is_saved' => 0 // Assegura que is_saved não seja alterado ao pedir publicação
            ]);
            $message = 'Pedido de publicação enviado com sucesso para avaliação.';
            $route = 'produtos.create'; // Ou outra rota conforme necessário

            // Adicionar o produto à tabela user_produto com mensagem automática
            $connection->table('user_produto')->updateOrInsert([
                'user_id' => $userId,
                'product_ref' => $produtoId,
                'recipient_id' => 1, // Definir o recipient_id como 1 (administrador)
                'mensagem' => 'Pedir publicação' // Mensagem automática ao pedir publicação
            ]);
        } else {
            // Marcar produto como guardado
            $connection->table($tabela)->where('ref', $produtoId)->update(['is_saved' => 1]);

            // Inserir o produto como guardado pelo usuário
            $connection->table('user_saved_products')->updateOrInsert([
                'user_id' => $userId,
                'product_ref' => $produtoId
            ]);

            $message = 'Produto guardado com sucesso.';
            $route = 'produtos.create';
        }

        // Não adicionar o produto à tabela user_produto para ações diferentes de 'pedir_publicacao'
        if ($action != 'pedir_publicacao') {
            $connection->table('user_produto')->where([
                'user_id' => $userId,
                'product_ref' => $produtoId,
            ])->delete();
        }

        return redirect()->route($route, ['conf' => $conf, 'tabela' => $tabela])->with('success', $message);
    }


    public function update(Request $request, $conf, $tabela, $id)
    {
        $connection = $this->createCustomDatabaseConnection($conf);

        // Remove the currency symbol, dots, and replace commas with dots for decimal format
        $valor = $request->input('valor');
        $valorLimpo = str_replace(['€', '.', ' '], ['', '', ''], $valor); // Remove € and dots
        $valorLimpo = str_replace(',', '.', $valorLimpo); // Replace comma with dot

        // Update the value in the request with the cleaned value
        $request->merge(['valor' => $valorLimpo]);

        // Validation of request data
        $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'valor' => 'required|numeric',
            'ativo' => 'required|boolean',
            'data' => 'nullable|date', // Validate as an optional date
            'imagem_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'nullable|file|max:2048',
            'id_tipo' => 'required|string', // Validate the id_tipo field
            'localizacao' => 'required|string', // Validate the localizacao field
            'valor_negociavel' => 'nullable|string', // Validate the valor_negociavel field
            'areas_actividade' => 'nullable|string', // Validate the areas_actividade field
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate multiple images
            'documentos_remover.*' => 'nullable|integer', // Validate document IDs to remove
            'imagens_remover.*' => 'nullable|integer', // Validate image IDs to remove
        ]);

        // Collect main product data
        $produtoData = $request->only(['titulo', 'descricao', 'valor', 'data', 'ativo', 'id_tipo', 'localizacao', 'valor_negociavel', 'areas_actividade']);

        // Update the main product data
        $connection->table($tabela)->where('ref', $id)->update($produtoData);

        // Handle the main image if provided
        if ($request->hasFile('imagem_principal')) {
            $produto = $connection->table($tabela)->where('ref', $id)->first();
            if ($produto->imagem_principal) {
                Storage::disk('public')->delete($produto->imagem_principal);
            }

            $path = $request->file('imagem_principal')->store('public/imagens');
            $connection->table($tabela)->where('ref', $id)->update(['imagem_principal' => str_replace('public/', '', $path)]);
        }

        // Handle the removal of selected documents
        if ($request->has('documentos_remover')) {
            foreach ($request->input('documentos_remover') as $documentoId) {
                $documento = $connection->table('documentos')->where('id', $documentoId)->first();
                if ($documento) {
                    // Remove the file from the disk
                    Storage::disk('public')->delete('documentos/' . $documento->arquivo);
                    // Remove the record from the database
                    $connection->table('documentos')->where('id', $documentoId)->delete();
                }
            }
        }

        // Handle the addition of new documents if provided
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('public/documentos');
                // Insert the document associated with the product in the database
                $connection->table('documentos')->insert([
                    'ref' => $id,
                    'arquivo' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(), // Store the original file name
                ]);
            }
        }

        // Handle the removal of selected images
        if ($request->has('imagens_remover')) {
            foreach ($request->input('imagens_remover') as $imagemId) {
                $imagem = $connection->table('produto_imagens')->where('id', $imagemId)->first();
                if ($imagem) {
                    // Remove the file from the disk
                    Storage::disk('public')->delete('imagens/' . $imagem->caminho);
                    // Remove the record from the database
                    $connection->table('produto_imagens')->where('id', $imagemId)->delete();
                }
            }
        }

        // Handle the addition of new images if provided
        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $file) {
                $path = $file->store('public/imagens');
                // Insert the image associated with the product in the database
                $connection->table('produto_imagens')->insert([
                    'produto_ref' => $id,
                    'caminho' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(), // Store the original file name
                ]);
            }
        }

        return redirect()->route('produtos', [$conf, $tabela])->with('success', 'Produto atualizado com sucesso.');
    }

    public function updateUser(Request $request, $conf, $tabela, $id)
    {
        $connection = $this->createCustomDatabaseConnection($conf);

        // Remove the currency symbol, dots, and replace commas with dots for decimal format
        $valor = $request->input('valor');
        $valorLimpo = str_replace(['€', '.', ' '], ['', '', ''], $valor); // Remove € and dots
        $valorLimpo = str_replace(',', '.', $valorLimpo); // Replace comma with dot

        // Update the value in the request with the cleaned value
        $request->merge(['valor' => $valorLimpo]);

        // Validation of request data
        $request->validate([
            'titulo' => 'required',
            'descricao' => 'required',
            'valor' => 'required|numeric',
            'ativo' => 'required|boolean',
            'data' => 'nullable|date', // Validate as an optional date
            'imagem_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'nullable|file|max:2048',
            'id_tipo' => 'required|string', // Validate the id_tipo field
            'localizacao' => 'required|string', // Validate the localizacao field
            'valor_negociavel' => 'nullable|string', // Validate the valor_negociavel field
            'areas_actividade' => 'nullable|string', // Validate the areas_actividade field
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate multiple images
            'documentos_remover.*' => 'nullable|integer', // Validate document IDs to remove
            'imagens_remover.*' => 'nullable|integer', // Validate image IDs to remove
        ]);

        // Collect main product data
        $produtoData = $request->only(['titulo', 'descricao', 'valor', 'data', 'ativo', 'id_tipo', 'localizacao', 'valor_negociavel', 'areas_actividade']);

        // Update the main product data
        $connection->table($tabela)->where('ref', $id)->update($produtoData);

        // Handle the main image if provided
        if ($request->hasFile('imagem_principal')) {
            $produto = $connection->table($tabela)->where('ref', $id)->first();
            if ($produto->imagem_principal) {
                Storage::disk('public')->delete($produto->imagem_principal);
            }

            $path = $request->file('imagem_principal')->store('public/imagens');
            $connection->table($tabela)->where('ref', $id)->update(['imagem_principal' => str_replace('public/', '', $path)]);
        }

        // Handle the removal of selected documents
        if ($request->has('documentos_remover')) {
            foreach ($request->input('documentos_remover') as $documentoId) {
                $documento = $connection->table('documentos')->where('id', $documentoId)->first();
                if ($documento) {
                    // Remove the file from the disk
                    Storage::disk('public')->delete('documentos/' . $documento->arquivo);
                    // Remove the record from the database
                    $connection->table('documentos')->where('id', $documentoId)->delete();
                }
            }
        }

        // Handle the addition of new documents if provided
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('public/documentos');
                // Insert the document associated with the product in the database
                $connection->table('documentos')->insert([
                    'ref' => $id,
                    'arquivo' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(), // Store the original file name
                ]);
            }
        }

        // Handle the removal of selected images
        if ($request->has('imagens_remover')) {
            foreach ($request->input('imagens_remover') as $imagemId) {
                $imagem = $connection->table('produto_imagens')->where('id', $imagemId)->first();
                if ($imagem) {
                    // Remove the file from the disk
                    Storage::disk('public')->delete('imagens/' . $imagem->caminho);
                    // Remove the record from the database
                    $connection->table('produto_imagens')->where('id', $imagemId)->delete();
                }
            }
        }

        // Handle the addition of new images if provided
        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $file) {
                $path = $file->store('public/imagens');
                // Insert the image associated with the product in the database
                $connection->table('produto_imagens')->insert([
                    'produto_ref' => $id,
                    'caminho' => str_replace('public/', '', $path),
                    'nome_original' => $file->getClientOriginalName(), // Store the original file name
                ]);
            }
        }

        // Determinar a ação com base no input
        $action = $request->input('action');
        $userId = Auth::id(); // Obter o ID do usuário autenticado

        if ($action == 'pedir_publicacao') {
            // Atualizar produto para pedido de publicação
            $connection->table($tabela)->where('ref', $id)->update([
                'pedir_publicacao' => true,
                'is_saved' => 0 // Assegura que is_saved não seja alterado ao pedir publicação
            ]);

            $connection->table('user_produto')->updateOrInsert([
                'user_id' => $userId,
                'product_ref' => $id,
                'recipient_id' => 1, // Definir o recipient_id como 1 (administrador)
                'mensagem' => 'Pedir publicação' // Mensagem automática ao pedir publicação
            ]);
        }



        return redirect()->route('anuncios.pedidos_publicacao', ['conf' => $conf, 'tabela' => $tabela])->with('success', 'Anuncio atualizado com sucesso e pedido efectuado.');
    }


    public function destroy($conf, $tabela, $id)
    {
        $connection = $this->createCustomDatabaseConnection($conf);
        $produto = $connection->table($tabela)->where('ref', $id)->first();

        if ($produto->imagem_principal) {
            Storage::disk('public')->delete($produto->imagem_principal);
        }

        $documentos = $connection->table('documentos')->where('ref', $id)->get();
        foreach ($documentos as $documento) {
            Storage::disk('public')->delete($documento->arquivo);
            $connection->table('documentos')->where('id', $documento->id)->delete();
        }

        $connection->table($tabela)->where('ref', $id)->delete();

        return redirect()->route('produtos', [$conf, $tabela])->with('success', 'Produto apagado com sucesso.');
    }

    public function downloadDocumento($conf, $tabela, $id)
    {
        // Cria a conexão personalizada
        $connection = $this->createCustomDatabaseConnection($conf);

        // Define a tabela
        $table = $tabela;

        // Realiza a consulta
        $documento = $connection->table($table)->where('id', $id)->first();

        // Verifica se o documento foi encontrado
        if (!$documento) {
            abort(404, 'Documento não encontrado');
        }

        // Retorno do download do documento
        return response()->download(storage_path('app/public/' . $documento->arquivo));
    }

    public function mostrarInteresse($conf, $tabela, $ref)
    {
        // Verifique se o usuário está autenticado e obtenha o ID do usuário
        $userId = auth()->id();

        if (!$userId) {
            return redirect()->back()->with('error', 'Você precisa estar logado para registrar interesse.');
        }

        // Conecte-se ao banco de dados
        $connection = $this->createCustomDatabaseConnection($conf);

        // Verifique se o produto existe
        $produto = $connection->table($tabela)->where('ref', $ref)->first();

        if (!$produto) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        // Verifique se o usuário já demonstrou interesse no produto
        $interesseExistente = $connection->table('contactos')
            ->where('ref', $ref)
            ->where('user', $userId)
            ->exists();

        if ($interesseExistente) {
            return redirect()->back()->with('info', 'Você já demonstrou interesse neste produto.');
        }

        // Registre o interesse do usuário no produto
        $connection->table('contactos')->insert([
            'ref' => $ref,
            'user' => $userId,
            'data' => now()->toDateString(), // Usa a data atual
            'concluido' => 0, // Define como não concluído por padrão
        ]);

        return redirect()->back()->with('success', 'Seu interesse no produto foi registrado com sucesso.');
    }

    // ProdutoController.php
    public function toggleFavorite(Request $request, $conf, $tabela, $ref)
    {
        // Retrieve the authenticated user's ID
        $userId = $request->user()->id;

        // Create a custom database connection for user favorites and product
        $userConnection = $this->createCustomDatabaseConnection('produtos');
        $productConnection = $this->createCustomDatabaseConnection('produtos');

        // Retrieve the product and check if it exists
        $produto = $productConnection->table($tabela)->where('ref', $ref)->first();

        if ($produto) {
            // Check if the product is already favorited by the user
            $favorite = $userConnection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $ref)
                ->first();

            if ($favorite) {
                // Remove from favorites if it exists
                $userConnection->table('user_favorites')
                    ->where('user_id', $userId)
                    ->where('product_ref', $ref)
                    ->delete();

                // Update the `is_favorite` column to 0
                $productConnection->table($tabela)
                    ->where('ref', $ref)
                    ->update(['is_favorite' => 0]);

                $message = 'Retirou este produto dos favoritos.';
            } else {
                // Add to favorites if it does not exist
                $userConnection->table('user_favorites')->insert([
                    'user_id' => $userId,
                    'product_ref' => $ref
                ]);

                // Update the `is_favorite` column to 1
                $productConnection->table($tabela)
                    ->where('ref', $ref)
                    ->update(['is_favorite' => 1]);

                $message = 'Adicionou este produto aos favoritos.';
            }

            // Redirect back with the appropriate success message
            return redirect()->back()->with('success', $message);
        }

        // Redirect back with an error message if the product is not found
        return redirect()->back()->with('error', 'Produto não encontrado.');
    }


    public function favoritos(Request $request, $conf)
    {
        // Obter o ID do usuário autenticado
        $userId = $request->user()->id;

        // Conexão personalizada com o banco de dados de produtos
        $productConnection = $this->createCustomDatabaseConnection('produtos');

        // Conexão personalizada com o banco de dados de favoritos
        $userConnection = $this->createCustomDatabaseConnection($conf);

        // Recuperar referências dos produtos favoritos do usuário usando a conexão personalizada
        $favoriteRefs = $userConnection->table('user_favorites')
            ->where('user_id', $userId)
            ->pluck('product_ref');

        // Recuperar produtos favoritos usando a conexão personalizada e as referências
        $favoritos = $productConnection->table('produto')
            ->whereIn('ref', $favoriteRefs)
            ->get();

        // Retornar a visão com os produtos favoritos
        return view('favoritos.layout.dashboard', compact('favoritos', 'conf'));
    }

    public function avaliacao(Request $request, $conf, $tabela)
    {
        $userRole = Auth::user()->role;
        $userId = Auth::id();

        // Cria uma conexão personalizada para a tabela de produtos e anúncios
        $connection = $this->createCustomDatabaseConnection($conf);

        // Obtém os anúncios que têm pedido de publicação
        $anuncios = collect($connection->table($tabela)
            ->where('pedir_publicacao', true)
            ->get()); // Força a conversão para Collection

        // Obter o tipo de produto selecionado
        $tipoSelecionado = $request->input('tipo_produto');

        // Buscar tipos de produto para o dropdown
        $tipos = collect($connection->table('tipo_produto')->get());

        $produtos = collect($connection->table('produto')
            ->leftJoin('user_produto', 'produto.ref', '=', 'user_produto.product_ref')
            ->select('produto.*', 'user_produto.user_id')
            ->where('produto.pedir_publicacao', true)
            ->get())
            ->unique('ref'); // Filtra resultados únicos com base na referência do produto



        // Cria uma conexão personalizada para buscar usuários
        $customConnection2 = $this->createCustomDatabaseConnection('consultingcast3');
        $users = collect($customConnection2->select("SELECT id, name FROM users"));

        // Mapeia os usuários para um array para fácil acesso
        $usersMap = $users->keyBy('id');

        // Adiciona o nome do usuário aos produtos
        foreach ($produtos as $produto) {
            $user = $usersMap->get($produto->user_id);
            $produto->user_name = $user ? $user->name : 'Desconhecido';
        }

        // Criar uma segunda conexão para a tabela de documentos e imagens
        $customConnection = $this->createCustomDatabaseConnection($conf);

        $produtosComDocumentos = [];

        foreach ($produtos as $produto) {
            $documentos = collect($customConnection->table('documentos')
                ->where('ref', $produto->ref)
                ->get());

            $imagens = collect($customConnection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get());

            $produto->documentos = $documentos;
            $produto->imagens = $imagens;

            $produtosComDocumentos[] = $produto;
        }
        $productRefQuery = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->first();

        // Definir o product_ref
        $productRef = $productRefQuery ? $productRefQuery->product_ref : null;

        // Se o product_ref for null, você pode atribuir um valor padrão ou tratar a ausência
        if ($productRef === null) {
            $productRef = '0'; // Ou qualquer valor padrão que faça sentido para sua aplicação
        }

        $pendingAdsCount = $connection->table($tabela)
            ->where('pedir_publicacao', true)
            ->count();

        $messagesQuery = $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('is_read', false);

        // Excluir mensagens de "pedido de publicação" para usuários não administradores
        if ($userId != 1) {
            $messagesQuery->where('mensagem', '!=', 'Pedir publicação');
        }

        $newMessagesCount = $messagesQuery->count();

        return view('anuncios.avaliacao', [
            'anuncios' => $anuncios,
            'conf' => $conf,
            'tabela' => $tabela,
            'tipos' => $tipos,
            'userRole' => $userRole,
            'tipoSelecionado' => $tipoSelecionado,
            'produtos' => $produtosComDocumentos,
            'pendingAdsCount' => $pendingAdsCount,
            'productRef' => $productRef,
            'newMessagesCount' => $newMessagesCount,
        ]);
    }


    public function publicar($conf, $tabela, $id)
    {
        $connection = $this->createCustomDatabaseConnection($conf);

        // Verifica se o produto pode ser publicado
        $produto = $connection->table($tabela)->where('ref', $id)->first();

        if ($produto) {
            if ($produto->pedir_publicacao) {
                // Atualiza o produto para publicado e remove o pedido de publicação
                $connection->table($tabela)->where('ref', $id)->update([
                    'publicado' => true,
                    'pedir_publicacao' => false, // Remove o pedido de publicação após a publicação
                ]);

                return redirect()->route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela])->with('success', 'Anúncio publicado com sucesso.');
            } else {
                return redirect()->route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela])->with('error', 'Anúncio não pode ser publicado porque não solicitou publicação.');
            }
        } else {
            return redirect()->route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela])->with('error', 'Anúncio não encontrado.');
        }
    }

    public function deletar($conf, $tabela, $id)
    {
        $connection = $this->createCustomDatabaseConnection($conf);

        // Verifica se o produto existe antes de tentar deletar
        $produto = $connection->table($tabela)->where('ref', $id)->first();

        if ($produto) {
            // Deleta o produto
            $connection->table($tabela)->where('ref', $id)->delete();
            return redirect()->route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela])->with('success', 'Anúncio excluído com sucesso.');
        } else {
            return redirect()->route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela])->with('error', 'Anúncio não encontrado.');
        }
    }

    public function guardados(Request $request, $conf, $tabela)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Criar a conexão para a tabela de produtos
        $connection = $this->createCustomDatabaseConnection($conf);

        // Obter o tipo de produto selecionado
        $tipoSelecionado = $request->input('tipo_produto');

        // Buscar produtos guardados pelo usuário atual
        $produtosGuardados = $connection->table('user_saved_products') // Substitua 'user_saved_products' pelo nome da tabela que armazena os produtos guardados
        ->where('user_id', $userId)
            ->pluck('product_ref'); // Obtém apenas as referências dos produtos guardados

        // Construir a consulta para buscar os detalhes dos produtos guardados
        $query = $connection->table($tabela)
            ->whereIn('ref', $produtosGuardados); // Filtra apenas os produtos guardados pelo usuário

        // Aplicar o filtro se um tipo estiver selecionado
        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        // Buscar produtos
        $produtos = $query->get();

        // Criar uma segunda conexão para a tabela de documentos, imagens e favoritos
        $customConnection = $this->createCustomDatabaseConnection($conf);
        $userConnection = $this->createCustomDatabaseConnection('produtos');

        // Inicializar arrays para armazenar documentos e imagens agrupados por produto
        $produtosComDocumentos = [];
        $produtosComImagens = [];

        // Iterar sobre cada produto para buscar seus documentos, imagens e favoritos associados
        foreach ($produtos as $produto) {
            // Buscar documentos para o produto atual
            $documentos = $customConnection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            // Buscar imagens para o produto atual
            $imagens = $customConnection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();

            // Verificar se o produto é favorito para o usuário atual
            $isFavorite = $userConnection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $produto->ref)
                ->exists();

            // Adicionar documentos, imagens e estado de favorito ao produto atual
            $produto->documentos = $documentos;
            $produto->imagens = $imagens;
            $produto->is_favorite = $isFavorite;

            // Adicionar o produto com documentos, imagens e estado de favorito ao array
            $produtosComDocumentos[] = $produto;
        }

        // Buscar tipos de produto para o dropdown
        $tipos = $connection->table('tipo_produto')->get();

        $data = [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => str_replace('_', '', $conf),
            'conf' => $conf,
            'tabela' => $tabela,
            'produtos' => $produtosComDocumentos,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado, // Passar o tipo selecionado para a view
            'userId' => $userId,
            'userRole' => $userRole,
        ];

        return view('anunciosguardados.layout.dashboard', $data);
    }


}
