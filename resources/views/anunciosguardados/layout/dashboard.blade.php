@extends('anunciosguardados.layout.app')

@section('main-content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* CSS customizado como anteriormente */
        .custom-blue-btn {
            background-color: #1A73E8;
            color: #fff;
        }

        .custom-blue-btn:hover {
            background-color: #1A73E8;
            color: #fff;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            padding: 15px;
            text-align: center;
            position: relative;
            color: #000;
            background-color: #fff;
            width: 100%;
            max-width: 500px;
        }

        .product-card img {
            max-width: 100%;
            height: auto;
        }

        .product-card .title {
            font-weight: bold;
            margin-top: 10px;
            color: #003366;
            font-size: 1.5em;
        }

        .product-card .info {
            margin-top: 10px;
            color: #000;
        }

        .product-card .info .valor {
            color: #1A73E8;
            font-weight: bold;
        }

        .product-card .descricao-view {
            text-align: left;
            line-height: 1.6;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #f8f9fa;
            color: #000;
            display: none;
            max-height: 150px;
            overflow-y: auto;
        }

        .product-card .more-info-btn {
            margin-top: 10px;
            cursor: pointer;
            color: #fff;
            background-color: #007bff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .product-card .more-info-btn:hover {
            background-color: #0056b3;
        }

        .product-card .action-buttons {
            margin-top: 10px;
        }

        .product-card .action-buttons form {
            display: inline-block;
        }

        .dropdown-menu {
            text-align: left;
        }

        .dropdown-container {
            display: none;
            margin-top: 10px;
        }

        .gallery {
            margin-top: 10px;
        }

        .gallery img {
            max-width: 90%;
            height: auto;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .carousel-inner img {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: cover;
        }

        .favorite-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 24px;
            color: #ddd;
        }

        .favorite-icon i.fas {
            color: #e74c3c;
        }

        .image-container {
            position: relative;
            display: inline-block;
        }

        .image-container img {
            display: block;
            width: 100%;
            height: auto;
        }

        .badge-negociacao {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: red;
            color: #fff;
            padding: 5px 0;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
            z-index: 10;
        }

        .non-negociavel {
            background-color: #6c757d;
        }

        .no-announcements {
            text-align: center;
            font-size: 1.2em;
            color: #666;
            margin-top: 20px;
        }
    </style>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
        @endif

        @if (empty($produtos))
            <div class="no-announcements">
                Não há anúncios guardados.
            </div>
        @else
            <div class="row">
                @foreach ($produtos as $produto)
                    <div class="col-md-4">
                        <div class="product-card">
                            <div class="image">
                                @if ($produto->imagem_principal)
                                    <div class="image-container">
                                        <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->titulo }}">
                                        @if ($produto->valor_negociavel == 'Negociável')
                                            <div class="badge-negociacao">Negociável</div>
                                        @else
                                            <div class="badge-negociacao non-negociavel">Não Negociável</div>
                                        @endif
                                    </div>
                                @else
                                    <span>Nenhuma imagem disponível</span>
                                @endif
                                <div class="favorite-icon"
                                     onclick="document.getElementById('favorite-form-{{ $produto->ref }}').submit()">
                                    <i class="{{ $produto->is_favorite ? 'fas' : 'far' }} fa-heart"></i>
                                </div>
                            </div>

                            <div class="title">{{ $produto->titulo }}</div>
                            <div class="info">
                                Valor Solicitado <span
                                    class="valor">{{ number_format($produto->valor, 2, ',', '.') }}€</span>
                            </div>
                            <div class="negociacao">
                                <span
                                    class="badge {{ $produto->valor_negociavel == 'Negociável' ? 'badge-success' : 'badge-secondary' }}">{{ $produto->valor_negociavel }}</span>
                            </div>
                            <!-- Exemplo de visualização da descrição -->
                            <div class="descricao-view">
                                {!! nl2br(e($produto->descricao)) !!}
                            </div>

                            <div class="more-info-btn" onclick="toggleDescription(this)">Mais informações</div>

                            <div class="dropdown-container">
                                <div class="dropdown mt-3 d-inline-block">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton-{{ $produto->ref }}" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        Documentos
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $produto->ref }}">
                                        @foreach ($produto->documentos as $documento)
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ asset('storage/' . $documento->arquivo) }}"
                                                   download="{{ $documento->nome_original }}">
                                                    {{ $documento->nome_original }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                @if ($produto->imagens->count())
                                    <div id="carousel-{{ $produto->ref }}" class="carousel slide mt-3">
                                        <div class="carousel-inner">
                                            @foreach ($produto->imagens as $index => $imagem)
                                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                    <img src="{{ asset('storage/' . $imagem->caminho) }}" class="d-block w-100"
                                                         alt="{{ $imagem->nome_original }}">
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carousel-{{ $produto->ref }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                                data-bs-target="#carousel-{{ $produto->ref }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if ($userRole === 'manager')
                                <div class="action-buttons">
                                    <a href="{{ route('produtos.edit', [$conf, $tabela, $produto->ref]) }}"
                                       class="btn custom-blue-btn">Editar</a>
                                    <form action="{{ route('produtos.destroy', [$conf, $tabela, $produto->ref]) }}"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </div>
                            @endif

                            <form id="favorite-form-{{ $produto->ref }}"
                                  action="{{ route('produtos.toggle_favorite', [$conf, $tabela, $produto->ref]) }}"
                                  method="POST" style="display: none;">
                                @csrf
                                @method('PUT')
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Adiciona um ouvinte de evento a todos os botões de "Mais informações"
            document.querySelectorAll('.more-info-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const card = button.closest('.product-card');
                    const description = card.querySelector('.descricao-view');
                    const dropdownContainer = card.querySelector('.dropdown-container');

                    // Alterna a exibição da descrição e do container de documentos
                    if (description.style.display === 'none' || description.style.display === '') {
                        description.style.display = 'block';
                        dropdownContainer.style.display = 'block';
                        button.innerText = 'Menos informações';
                    } else {
                        description.style.display = 'none';
                        dropdownContainer.style.display = 'none';
                        button.innerText = 'Mais informações';
                    }
                });
            });

            // Adiciona um ouvinte de evento a todos os ícones de favoritos
            document.querySelectorAll('.favorite-icon').forEach(icon => {
                icon.addEventListener('click', function () {
                    const produtoRef = this.dataset.ref;
                    const isFavorite = this.classList.contains('fas');

                    // Enviar a solicitação AJAX para alternar o estado de favorito
                    fetch(`{{ url('/produtos/' . $conf . '/' . $tabela . '/') }}${produtoRef}/toggle_favorite`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Alterna o ícone com base no estado de favorito
                                this.querySelector('i').classList.toggle('fas', !isFavorite);
                                this.querySelector('i').classList.toggle('far', isFavorite);
                            } else {
                                console.error('Erro ao alternar o estado de favorito.');
                            }
                        })
                        .catch(error => console.error('Erro:', error));
                });
            });
        });
    </script>
@endsection
