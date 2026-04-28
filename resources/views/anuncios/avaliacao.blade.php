@extends('novo_dashboard.layout.app')

@section('main-content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            width: 100%; /* Garante que o cartão ocupe toda a largura disponível */
            max-width: 500px; /* Define uma largura máxima para o cartão */
        }

        .product-card img {
            max-width: 100%;
            height: auto;
        }

        .product-card .title {
            font-weight: bold;
            margin-top: 10px;
            color: #003366; /* Azul escuro para o título */
            font-size: 1.5em; /* Tamanho maior da fonte para o título */
        }

        .product-card .info {
            margin-top: 10px;
            color: #000; /* Cor padrão do texto dentro da classe .info */
        }

        .product-card .info .valor {
            color: #1A73E8; /* Azul claro para o valor solicitado */
            font-weight: bold;
        }

        .product-card .descricao-view {
            text-align: left; /* Alinha o texto à esquerda */
            line-height: 1.6; /* Espaçamento entre linhas para melhorar a legibilidade */
            padding: 10px; /* Adiciona um pouco de preenchimento para espaçamento */
            border: 1px solid #ced4da; /* Adiciona uma borda leve para destacar a área */
            border-radius: 4px; /* Adiciona bordas arredondadas */
            background-color: #f8f9fa; /* Cor de fundo leve para a área de descrição */
            color: #000; /* Cor do texto da descrição */
            display: none; /* Inicialmente escondido */
            max-height: 150px; /* Limita a altura da descrição */
            overflow-y: auto; /* Adiciona rolagem vertical se o conteúdo for maior que a altura */
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
            color: #e74c3c; /* Vermelho para o ícone favorito */
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

        .btn-red {
            color: #fff; /* Cor do texto branco */
            background-color: #dc3545; /* Cor de fundo vermelho */
        }

        .btn-red:hover {
            background-color: #c82333; /* Cor de fundo vermelho mais escuro ao passar o mouse */
        }

        .badge-negociacao {
            position: absolute;
            bottom: 0; /* Posiciona o badge na base do cartão */
            left: 0; /* Alinha o badge à esquerda do cartão */
            width: 100%; /* Faz com que o badge ocupe toda a largura do cartão */
            background-color: red; /* Cor de fundo vermelha */
            color: #fff; /* Cor do texto branco */
            padding: 5px 0; /* Padding superior e inferior; ajuste se necessário */
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center; /* Centraliza o texto dentro do badge */
            z-index: 10; /* Garante que o badge fique acima da imagem */
        }

        .non-negociavel {
            background-color: #6c757d; /* Cinza para não negociável */
        }
    </style>


    <div class="container">
        <h1>Anúncios para Avaliação</h1>
        @if($anuncios->isEmpty())
            <p>Não há anúncios para avaliação.</p>
        @else
            <div class="row">
                @foreach ($produtos as $produto)
                    <div class="product-card">
                        <div class="image-container">
                            @if ($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}"
                                     alt="{{ $produto->titulo }}" class="img-fluid">
                            @else
                                <span>Nenhuma imagem disponível</span>
                            @endif
                        </div>
                        <div class="title">{{ $produto->titulo }}</div>
                        <div class="info">
                            Valor Solicitado <span
                                class="valor">{{ number_format($produto->valor, 2, ',', '.') }}€</span>
                        </div>
                        <div class="user-info">
                            <strong>Responsável:</strong> {{ $produto->user_name }}
                        </div>
                        <button class="more-info-btn">Mais informações</button>
                        <div class="descricao-view">
                            {!! nl2br(e($produto->descricao)) !!}
                        </div>

                        <!-- Dropdown de Documentos para cada produto -->
                        <div class="dropdown-container">
                            <div class="dropdown mt-3 d-inline-block">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                        id="dropdownMenuButton-{{ $produto->ref }}" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    Documentos
                                </button>
                                <ul class="dropdown-menu"
                                    aria-labelledby="dropdownMenuButton-{{ $produto->ref }}">
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
                        </div>

                        <div class="action-buttons">
                            <form action="{{ route('anuncios.publicar', [$conf, $tabela, $produto->ref]) }}"
                                  method="POST" class="d-inline-block me-2">
                                @csrf
                                <button type="submit" class="btn custom-blue-btn">Publicar</button>
                            </form>
                            <form action="{{ route('chat.iniciar') }}" method="POST" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="produto_ref" value="{{ $produto->ref }}">
                                <input type="hidden" name="conf" value="produtos">
                                <input type="hidden" name="tabela" value="produto">
                                <button type="submit" class="btn btn-red">Não publicar</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Função para alternar a exibição da descrição
            function toggleDescription(button) {
                const card = button.closest('.product-card');
                const description = card.querySelector('.descricao-view');
                const dropdownContainer = card.querySelector('.dropdown-container');

                if (description.style.display === 'none' || description.style.display === '') {
                    description.style.display = 'block';
                    dropdownContainer.style.display = 'block';
                    button.innerText = 'Menos informações';
                } else {
                    description.style.display = 'none';
                    dropdownContainer.style.display = 'none';
                    button.innerText = 'Mais informações';
                }
            }

            // Adiciona um ouvinte de evento a todos os botões de "Mais informações"
            document.querySelectorAll('.more-info-btn').forEach(button => {
                button.addEventListener('click', function () {
                    toggleDescription(this);
                });
            });

            // Adiciona um ouvinte de evento a todos os ícones de favoritos
            document.querySelectorAll('.favorite-icon').forEach(icon => {
                icon.addEventListener('click', function () {
                    const produtoRef = this.closest('.product-card').querySelector('form').getAttribute('id').split('-').pop();
                    const isFavorite = this.querySelector('i').classList.contains('fas');

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
                                this.querySelector('i').classList.toggle('fas', !isFavorite);
                                this.querySelector('i').classList.toggle('far', isFavorite);
                            } else {
                                console.error('Erro ao alternar o estado de favorito.');
                            }
                        })
                        .catch(error => console.error('Erro:', error));
                });
            });

            // Função para remover a notificação após um determinado tempo
            function removeAlert(alertId, timeout = 5000) {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    setTimeout(() => {
                        alertElement.style.transition = 'opacity 0.5s ease-out';
                        alertElement.style.opacity = 0;
                        setTimeout(() => alertElement.remove(), 500);
                    }, timeout);
                }
            }

            // Remove as notificações após um determinado tempo
            removeAlert('alert-success');
            removeAlert('alert-error');
            removeAlert('alert-info');
        });
    </script>
@endsection
