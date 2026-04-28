<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Produtos extends Controller
{
    private string $database = 'consultingcast3';
    private string $produtoTable = 'produto';

    private function connection()
    {
        return DB::connection('mysql');
    }

    private function normalizeValor(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valorLimpo = str_replace(['€', '.', ' '], ['', '', ''], $valor);
        return str_replace(',', '.', $valorLimpo);
    }

    public function index(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();
        $userId = Auth::id();
        $userRole = Auth::user()->role ?? null;

        $tipoSelecionado = $request->input('tipo_produto');

        $query = $connection->table($this->produtoTable);

        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        $produtos = $query->get();

        foreach ($produtos as $produto) {
            $produto->documentos = $connection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            $produto->imagens = $connection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();

            $produto->is_favorite = $connection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $produto->ref)
                ->exists();
        }

        $productRefsQuery = $connection->table('user_produto');

        if ($userId != 1) {
            $productRefsQuery->where('user_id', $userId);
        }

        $productRefs = $productRefsQuery->pluck('product_ref')->unique();

        $productRef = $connection->table('user_produto')
            ->where('user_id', $userId)
            ->value('product_ref') ?? '0';

        $pendingAdsCount = $connection->table($this->produtoTable)
            ->where('pedir_publicacao', true)
            ->count();

        $messagesQuery = $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('is_read', false);

        if ($userId != 1) {
            $messagesQuery->where('mensagem', '!=', 'Pedir publicação');
        }

        $newMessagesCount = $messagesQuery->count();

        $tipos = $connection->table('tipo_produto')->get();

        return view('novo_dashboard.layout.dashboard', [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => 'consultingcast3',
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
            'produtos' => $produtos,
            'tipos' => $tipos,
            'tipoSelecionado' => $tipoSelecionado,
            'userId' => $userId,
            'userRole' => $userRole,
            'pendingAdsCount' => $pendingAdsCount,
            'productRef' => $productRef,
            'productRefs' => $productRefs,
            'newMessagesCount' => $newMessagesCount,
        ]);
    }

    public function create(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        $tipoSelecionado = $request->input('tipo_produto');

        $query = $connection->table($this->produtoTable);

        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        return view('produtos.create', [
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
            'userRole' => Auth::user()->role ?? null,
            'userId' => $userId,
            'tipos' => $connection->table('tipo_produto')->get(),
            'tipoSelecionado' => $tipoSelecionado,
            'produtos' => $query->get(),
            'productRef' => $connection->table('user_produto')->where('user_id', $userId)->value('product_ref') ?? '0',
        ]);
    }

    public function store(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();

        $request->merge([
            'valor' => $this->normalizeValor($request->input('valor')),
        ]);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'valor' => 'required|numeric',
            'ativo' => 'required|boolean',
            'data' => 'nullable|date',
            'imagem_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'nullable|file|max:2048',
            'id_tipo' => 'required|string',
            'localizacao' => 'required|string|max:255',
            'valor_negociavel' => 'nullable|string|max:255',
            'areas_actividade' => 'nullable|string',
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userId = Auth::id();
        $action = $request->input('action');

        DB::transaction(function () use ($request, $connection, $userId, $action, &$produtoId) {
            $produtoData = $request->only([
                'titulo',
                'descricao',
                'valor',
                'ativo',
                'id_tipo',
                'localizacao',
                'valor_negociavel',
                'areas_actividade',
            ]);

            $produtoData['data'] = $request->input('data');
            $produtoData['publicado'] = $action === 'publicar';
            $produtoData['pedir_publicacao'] = $action === 'pedir_publicacao';
            $produtoData['is_saved'] = !in_array($action, ['publicar', 'pedir_publicacao']);
            $produtoData['created_at'] = now();
            $produtoData['updated_at'] = now();

            $produtoId = $connection->table($this->produtoTable)->insertGetId($produtoData);

            if ($request->hasFile('imagem_principal')) {
                $path = $request->file('imagem_principal')->store('public/imagens');

                $connection->table($this->produtoTable)
                    ->where('ref', $produtoId)
                    ->update([
                        'imagem_principal' => str_replace('public/', '', $path),
                        'updated_at' => now(),
                    ]);
            }

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $file) {
                    $path = $file->store('public/documentos');

                    $connection->table('documentos')->insert([
                        'ref' => $produtoId,
                        'arquivo' => str_replace('public/', '', $path),
                        'nome_original' => $file->getClientOriginalName(),
                        'created_at' => now(),
                        'updated_at' => now(),
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
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($action === 'pedir_publicacao') {
                $connection->table('user_produto')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'product_ref' => $produtoId,
                    ],
                    [
                        'recipient_id' => 1,
                        'mensagem' => 'Pedir publicação',
                        'is_read' => false,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            if (!in_array($action, ['publicar', 'pedir_publicacao'])) {
                $connection->table('user_saved_products')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'product_ref' => $produtoId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        $message = match ($action) {
            'publicar' => 'Produto publicado com sucesso.',
            'pedir_publicacao' => 'Pedido de publicação enviado com sucesso para avaliação.',
            default => 'Produto guardado com sucesso.',
        };

        $route = $action === 'publicar' ? 'produtos' : 'produtos.create';

        return redirect()
            ->route($route, ['conf' => 'consultingcast3', 'tabela' => $this->produtoTable])
            ->with('success', $message);
    }

    public function edit(Request $request, $conf, $tabela, $id)
    {
        return $this->editView($request, $id, 'produtos.edit');
    }

    public function editForm(Request $request, $conf, $tabela, $id)
    {
        return $this->editView($request, $id, 'anuncioseditaruser.edit');
    }

    private function editView(Request $request, $id, string $view)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        $produto = $connection->table($this->produtoTable)
            ->where('ref', $id)
            ->first();

        abort_if(!$produto, 404, 'Produto não encontrado.');

        return view($view, [
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
            'produto' => $produto,
            'documentos' => $connection->table('documentos')->where('ref', $id)->get(),
            'imagens' => $connection->table('produto_imagens')->where('produto_ref', $id)->get(),
            'userRole' => Auth::user()->role ?? null,
            'userId' => $userId,
            'tipos' => $connection->table('tipo_produto')->get(),
            'tipoSelecionado' => $request->input('tipo_produto'),
            'productRef' => $connection->table('user_produto')->where('user_id', $userId)->value('product_ref'),
        ]);
    }

    public function update(Request $request, $conf, $tabela, $id)
    {
        return $this->updateProduto($request, $id, false);
    }

    public function updateUser(Request $request, $conf, $tabela, $id)
    {
        return $this->updateProduto($request, $id, true);
    }

    private function updateProduto(Request $request, $id, bool $pedidoPublicacao)
    {
        $connection = $this->connection();

        $request->merge([
            'valor' => $this->normalizeValor($request->input('valor')),
        ]);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'valor' => 'required|numeric',
            'ativo' => 'required|boolean',
            'data' => 'nullable|date',
            'imagem_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'nullable|file|max:2048',
            'id_tipo' => 'required|string|max:255',
            'localizacao' => 'required|string|max:255',
            'valor_negociavel' => 'nullable|string|max:255',
            'areas_actividade' => 'nullable|string',
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos_remover.*' => 'nullable|integer',
            'imagens_remover.*' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $connection, $id, $pedidoPublicacao) {
            $produtoData = $request->only([
                'titulo',
                'descricao',
                'valor',
                'data',
                'ativo',
                'id_tipo',
                'localizacao',
                'valor_negociavel',
                'areas_actividade',
            ]);

            $produtoData['updated_at'] = now();

            if ($pedidoPublicacao || $request->input('action') === 'pedir_publicacao') {
                $produtoData['pedir_publicacao'] = true;
                $produtoData['is_saved'] = false;
            }

            $connection->table($this->produtoTable)
                ->where('ref', $id)
                ->update($produtoData);

            if ($request->hasFile('imagem_principal')) {
                $produto = $connection->table($this->produtoTable)->where('ref', $id)->first();

                if ($produto && $produto->imagem_principal) {
                    Storage::disk('public')->delete($produto->imagem_principal);
                }

                $path = $request->file('imagem_principal')->store('public/imagens');

                $connection->table($this->produtoTable)
                    ->where('ref', $id)
                    ->update([
                        'imagem_principal' => str_replace('public/', '', $path),
                        'updated_at' => now(),
                    ]);
            }

            foreach ($request->input('documentos_remover', []) as $documentoId) {
                $documento = $connection->table('documentos')->where('id', $documentoId)->first();

                if ($documento) {
                    Storage::disk('public')->delete($documento->arquivo);
                    $connection->table('documentos')->where('id', $documentoId)->delete();
                }
            }

            foreach ($request->input('imagens_remover', []) as $imagemId) {
                $imagem = $connection->table('produto_imagens')->where('id', $imagemId)->first();

                if ($imagem) {
                    Storage::disk('public')->delete($imagem->caminho);
                    $connection->table('produto_imagens')->where('id', $imagemId)->delete();
                }
            }

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $file) {
                    $path = $file->store('public/documentos');

                    $connection->table('documentos')->insert([
                        'ref' => $id,
                        'arquivo' => str_replace('public/', '', $path),
                        'nome_original' => $file->getClientOriginalName(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $file) {
                    $path = $file->store('public/imagens');

                    $connection->table('produto_imagens')->insert([
                        'produto_ref' => $id,
                        'caminho' => str_replace('public/', '', $path),
                        'nome_original' => $file->getClientOriginalName(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($pedidoPublicacao || $request->input('action') === 'pedir_publicacao') {
                $connection->table('user_produto')->updateOrInsert(
                    [
                        'user_id' => Auth::id(),
                        'product_ref' => $id,
                    ],
                    [
                        'recipient_id' => 1,
                        'mensagem' => 'Pedir publicação',
                        'is_read' => false,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        if ($pedidoPublicacao) {
            return redirect()
                ->route('anuncios.pedidos_publicacao', ['conf' => 'consultingcast3', 'tabela' => $this->produtoTable])
                ->with('success', 'Anúncio atualizado com sucesso e pedido efectuado.');
        }

        return redirect()
            ->route('produtos', ['conf' => 'consultingcast3', 'tabela' => $this->produtoTable])
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy($conf, $tabela, $id)
    {
        $connection = $this->connection();

        $produto = $connection->table($this->produtoTable)->where('ref', $id)->first();

        abort_if(!$produto, 404, 'Produto não encontrado.');

        if ($produto->imagem_principal) {
            Storage::disk('public')->delete($produto->imagem_principal);
        }

        foreach ($connection->table('documentos')->where('ref', $id)->get() as $documento) {
            Storage::disk('public')->delete($documento->arquivo);
            $connection->table('documentos')->where('id', $documento->id)->delete();
        }

        foreach ($connection->table('produto_imagens')->where('produto_ref', $id)->get() as $imagem) {
            Storage::disk('public')->delete($imagem->caminho);
            $connection->table('produto_imagens')->where('id', $imagem->id)->delete();
        }

        $connection->table('user_produto')->where('product_ref', $id)->delete();
        $connection->table('user_saved_products')->where('product_ref', $id)->delete();
        $connection->table('user_favorites')->where('product_ref', $id)->delete();
        $connection->table('contactos')->where('ref', $id)->delete();

        $connection->table($this->produtoTable)->where('ref', $id)->delete();

        return redirect()
            ->route('produtos', ['conf' => 'consultingcast3', 'tabela' => $this->produtoTable])
            ->with('success', 'Produto apagado com sucesso.');
    }

    public function downloadDocumento($conf, $tabela, $id)
    {
        $documento = $this->connection()
            ->table('documentos')
            ->where('id', $id)
            ->first();

        abort_if(!$documento, 404, 'Documento não encontrado.');

        return response()->download(storage_path('app/public/' . $documento->arquivo));
    }

    public function mostrarInteresse($conf, $tabela, $ref)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->back()->with('error', 'Precisa de iniciar sessão para registar interesse.');
        }

        $produto = $connection->table($this->produtoTable)->where('ref', $ref)->first();

        if (!$produto) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        $exists = $connection->table('contactos')
            ->where('ref', $ref)
            ->where('user', $userId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('info', 'Já demonstrou interesse neste produto.');
        }

        $connection->table('contactos')->insert([
            'ref' => $ref,
            'user' => $userId,
            'data' => now()->toDateString(),
            'concluido' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Interesse registado com sucesso.');
    }

    public function toggleFavorite(Request $request, $conf, $tabela, $ref)
    {
        $connection = $this->connection();
        $userId = $request->user()->id;

        $produto = $connection->table($this->produtoTable)->where('ref', $ref)->first();

        if (!$produto) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        $favorite = $connection->table('user_favorites')
            ->where('user_id', $userId)
            ->where('product_ref', $ref)
            ->first();

        if ($favorite) {
            $connection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $ref)
                ->delete();

            $message = 'Retirou este produto dos favoritos.';
        } else {
            $connection->table('user_favorites')->insert([
                'user_id' => $userId,
                'product_ref' => $ref,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $message = 'Adicionou este produto aos favoritos.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function favoritos(Request $request, $conf = null)
    {
        $connection = $this->connection();
        $userId = $request->user()->id;

        $favoriteRefs = $connection->table('user_favorites')
            ->where('user_id', $userId)
            ->pluck('product_ref');

        $favoritos = $connection->table($this->produtoTable)
            ->whereIn('ref', $favoriteRefs)
            ->get();

        return view('favoritos.layout.dashboard', [
            'favoritos' => $favoritos,
            'conf' => 'consultingcast3',
        ]);
    }

    public function avaliacao(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        $tipoSelecionado = $request->input('tipo_produto');

        $produtos = $connection->table($this->produtoTable)
            ->leftJoin('user_produto', 'produto.ref', '=', 'user_produto.product_ref')
            ->select('produto.*', 'user_produto.user_id')
            ->where('produto.pedir_publicacao', true)
            ->get()
            ->unique('ref');

        $users = $connection->table('users')->select('id', 'name')->get()->keyBy('id');

        foreach ($produtos as $produto) {
            $produto->user_name = optional($users->get($produto->user_id))->name ?? 'Desconhecido';

            $produto->documentos = $connection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            $produto->imagens = $connection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();
        }

        $messagesQuery = $connection->table('user_produto')
            ->where('recipient_id', $userId)
            ->where('is_read', false);

        if ($userId != 1) {
            $messagesQuery->where('mensagem', '!=', 'Pedir publicação');
        }

        return view('anuncios.avaliacao', [
            'anuncios' => $produtos,
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
            'tipos' => $connection->table('tipo_produto')->get(),
            'userRole' => Auth::user()->role ?? null,
            'tipoSelecionado' => $tipoSelecionado,
            'produtos' => $produtos,
            'pendingAdsCount' => $connection->table($this->produtoTable)->where('pedir_publicacao', true)->count(),
            'productRef' => $connection->table('user_produto')->where('user_id', $userId)->value('product_ref') ?? '0',
            'newMessagesCount' => $messagesQuery->count(),
        ]);
    }

    public function publicar($conf, $tabela, $id)
    {
        $connection = $this->connection();

        $produto = $connection->table($this->produtoTable)->where('ref', $id)->first();

        if (!$produto) {
            return redirect()->back()->with('error', 'Anúncio não encontrado.');
        }

        if (!$produto->pedir_publicacao) {
            return redirect()->back()->with('error', 'Anúncio não pode ser publicado porque não solicitou publicação.');
        }

        $connection->table($this->produtoTable)->where('ref', $id)->update([
            'publicado' => true,
            'pedir_publicacao' => false,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('anuncios.avaliacao', ['conf' => 'consultingcast3', 'tabela' => $this->produtoTable])
            ->with('success', 'Anúncio publicado com sucesso.');
    }

    public function deletar($conf, $tabela, $id)
    {
        return $this->destroy($conf, $tabela, $id);
    }

    public function guardados(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        $tipoSelecionado = $request->input('tipo_produto');

        $refs = $connection->table('user_saved_products')
            ->where('user_id', $userId)
            ->pluck('product_ref');

        $query = $connection->table($this->produtoTable)
            ->whereIn('ref', $refs);

        if ($tipoSelecionado) {
            $query->where('id_tipo', $tipoSelecionado);
        }

        $produtos = $query->get();

        foreach ($produtos as $produto) {
            $produto->documentos = $connection->table('documentos')
                ->where('ref', $produto->ref)
                ->get();

            $produto->imagens = $connection->table('produto_imagens')
                ->where('produto_ref', $produto->ref)
                ->get();

            $produto->is_favorite = $connection->table('user_favorites')
                ->where('user_id', $userId)
                ->where('product_ref', $produto->ref)
                ->exists();
        }

        return view('anunciosguardados.layout.dashboard', [
            'nomeEmpresaSelecionada' => '',
            'nifatual' => 'consultingcast3',
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
            'produtos' => $produtos,
            'tipos' => $connection->table('tipo_produto')->get(),
            'tipoSelecionado' => $tipoSelecionado,
            'userId' => $userId,
            'userRole' => Auth::user()->role ?? null,
        ]);
    }

    public function pedidosPublicacao(Request $request, $conf = null, $tabela = null)
    {
        $connection = $this->connection();
        $userId = Auth::id();

        $anuncios = $connection->table($this->produtoTable)
            ->join('user_produto', 'user_produto.product_ref', '=', 'produto.ref')
            ->where('user_produto.user_id', $userId)
            ->where('produto.pedir_publicacao', false)
            ->select('produto.*')
            ->distinct()
            ->get()
            ->map(function ($anuncio) {
                $anuncio->data = $anuncio->data ? Carbon::parse($anuncio->data) : null;
                return $anuncio;
            });

        return view('anunciosparauser.layout.dashboard', [
            'anuncios' => $anuncios,
            'conf' => 'consultingcast3',
            'tabela' => $this->produtoTable,
        ]);
    }
}
