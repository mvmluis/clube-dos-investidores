<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Produtos;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/logout', function () {
    return view('dashboardlogout');
})->middleware(['auth', 'verified'])->name('custom-logout');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/produtos/{conf}/{tabela}/', [\App\Http\Controllers\Produtos::class, 'index'])->name('produtos');
    Route::get('produtos/create/{conf}/{tabela}', [Produtos::class, 'create'])->name('produtos.create');
    Route::post('produtos/store/{conf}/{tabela}', [Produtos::class, 'store'])->name('produtos.store');
    Route::get('produtos/edit/{conf}/{tabela}/{id}', [Produtos::class, 'edit'])->name('produtos.edit');
    Route::get('/produtos/{conf}/{tabela}/{id}/edit-form', [Produtos::class, 'editForm'])->name('produtos.editForm');
    Route::put('produtos/update/{conf}/{tabela}/{id}', [Produtos::class, 'update'])->name('produtos.update');
    Route::put('/user/update/{conf}/{tabela}/{id}', [Produtos::class, 'updateUser'])->name('user.update');
    Route::delete('produtos/destroy/{conf}/{tabela}/{id}', [Produtos::class, 'destroy'])->name('produtos.destroy');
    Route::get('download/{id}', [Produtos::class, 'downloadDocumento'])->name('download.documento');
    Route::get('utilizadores/{conf}/{tabela}', [\App\Http\Controllers\Utilizadores::class, 'utilizadores'])->name('utilizadores');
    Route::get('/produtos/{conf}/{tabela}/interesse/{ref}', [Produtos::class, 'mostrarInteresse'])->name('produtos.interesse');
    Route::get('/contactos/{conf}/{tabela}', [\App\Http\Controllers\ContactosController::class, 'index'])->name('contactos.index');
    Route::put('contactos/{id}', [\App\Http\Controllers\ContactosController::class, 'update'])->name('contactos.update');
    Route::put('/produtos/{conf}/{tabela}/{ref}/toggle_favorite', [\App\Http\Controllers\Produtos::class, 'toggleFavorite'])->name('produtos.toggle_favorite');
    Route::get('/favoritos/{conf}', [Produtos::class, 'favoritos'])->name('favoritos');
    Route::post('/utilizadores-propostos', [\App\Http\Controllers\Utilizadores::class, 'store'])->name('utilizadores_propostos.store');
    Route::get('/utilizadores.avaliacao/{conf}/{tabela}', [\App\Http\Controllers\Utilizadores::class, 'utilizadoresavaliacao'])->name('utilizadores.avaliacao');
    Route::post('/utilizadores_propostos/updateEstados', [\App\Http\Controllers\Utilizadores::class, 'updateEstados'])->name('utilizadores_propostos.updateEstados');
    Route::get('/utilizadores-ativos', [\App\Http\Controllers\Utilizadores::class, 'showActiveUsers'])->name('utilizadores_ativos');
    Route::get('/anuncios/avaliacao/{conf}/{tabela}', [\App\Http\Controllers\Produtos::class, 'avaliacao'])->name('anuncios.avaliacao');
    Route::post('/anuncios/publicar/{conf}/{tabela}/{id}', [\App\Http\Controllers\Produtos::class, 'publicar'])->name('anuncios.publicar');
    Route::delete('/anuncios/deletar/{conf}/{tabela}/{id}', [\App\Http\Controllers\Produtos::class, 'deletar'])->name('anuncios.deletar');
    Route::get('/anuncios/guardados/{conf}/{tabela}', [Produtos::class, 'guardados'])->name('anuncios.guardados');
    // Rota para exibir o chat
    Route::get('/chat/{conf}/{tabela}/{product_ref}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/select/{conf}/{tabela}/{product_ref}', [\App\Http\Controllers\ChatController::class, 'selectChat'])
        ->name('chat.select');
    Route::post('/chat/iniciar', [\App\Http\Controllers\ChatController::class, 'iniciar'])->name('chat.iniciar');
    Route::post('/chat/{conf}/{tabela}/{product_ref}', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{conf}/{tabela}/{product_ref}/{selectedUserId?}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::get('/anuncios/pedidos_publicacao/{conf}/{tabela}', [Produtos::class, 'pedidosPublicacao'])
        ->name('anuncios.pedidos_publicacao');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
