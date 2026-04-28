@extends('anunciosparauser.layout.app')
@section('main-content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mt-4">
        <h1>Meus Pedidos de Publicação</h1>

        @if ($anuncios->isEmpty())
            <p>Você não possui pedidos de publicação.</p>
        @else
            <div class="list-group">
                @foreach ($anuncios as $anuncio)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{{ $anuncio->titulo }}</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                @if ($anuncio->imagem_principal)
                                    <div class="image-container">
                                        <img src="{{ asset('storage/' . $anuncio->imagem_principal) }}"
                                             class="img-fluid" alt="{{ $anuncio->titulo }}">
                                    </div>
                                @else
                                    <p>Imagem não disponível</p>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <p class="mb-1"><strong>Descrição:</strong> {{ $anuncio->descricao }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ $anuncio->id_tipo }}</p>
                                <p class="mb-1"><strong>Áreas de Atividade:</strong> {{ $anuncio->areas_actividade }}
                                </p>
                                <p class="mb-1"><strong>Localização:</strong> {{ $anuncio->localizacao }}</p>
                                <p class="mb-1"><strong>Valor:</strong>
                                    € {{ number_format($anuncio->valor, 2, ',', '.') }}</p>
                                <p class="mb-1"><strong>Valor Negociável:</strong> {{ $anuncio->valor_negociavel }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ $anuncio->data->format('d/m/Y') }}</p>

                                <!-- Botão para editar o anúncio -->
                                <a href="{{ route('produtos.editForm', ['conf' => $conf, 'tabela' => $tabela, 'id' => $anuncio->ref]) }}"
                                   class="btn btn-primary mt-3">Editar Anúncio</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
