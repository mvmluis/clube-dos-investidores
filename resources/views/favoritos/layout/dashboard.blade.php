@extends('favoritos.layout.app')

@section('main-content')
    <div class="container mt-5">
        <h1 class="text-center">Produtos Favoritos</h1>
        <div class="row">
            @if($favoritos->isEmpty())
                <div class="col-12">
                    <p class="text-center">Nenhum produto foi adicionado aos favoritos ainda.</p>
                </div>
            @else
                @foreach($favoritos as $produto)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <!-- Verifica se há uma imagem principal, se não houver exibe um texto padrão -->
                            <img src="{{ $produto->imagem_principal ? asset('storage/' . $produto->imagem_principal) : 'https://via.placeholder.com/150' }}" class="card-img-top" alt="{{ $produto->titulo }}">
                            <div class="card-body text-center"> <!-- Adiciona a classe text-center para centralizar o conteúdo -->
                                <h5 class="card-title">{{ $produto->titulo }}</h5>
                                <p class="card-text"><strong>Valor:</strong> {{ number_format($produto->valor, 2, ',', '.') }}€</p>
                                <a href="{{ route('produtos', ['conf' => $conf, 'tabela' => 'produto', 'ref' => $produto->ref]) }}" class="btn btn-primary">Ver Produto</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
