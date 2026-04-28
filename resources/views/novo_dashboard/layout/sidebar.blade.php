@php
    use Illuminate\Support\Facades\Auth;
@endphp
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark d-lg-block d-xl-block"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="https://www.consultingcast.pt/" target="_blank">
            <img src="{{ asset('assets/img/clubeinvestidores.png') }}" class="navbar-brand-img img-fluid"
                 style="max-width: 190px; max-height: 300px;" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white" style="font-size: 13px;"></span>
        </a>
    </div>

    <hr class="horizontal light mt-7 mb-3">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            @if ($userRole === 'manager')
                <!-- Itens específicos para managers -->
                <li class="nav-item">
                    <a class="nav-link text-white"
                       href="{{ route('utilizadores.avaliacao', ['conf' => $conf, 'tabela' => $tabela]) }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="nav-link-text ms-1">Utilizadores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white"
                       href="{{ route('contactos.index', ['conf' => $conf, 'tabela' => $tabela]) }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span class="nav-link-text ms-1">Contactos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white"
                       href="{{ route('anuncios.avaliacao', ['conf' => $conf, 'tabela' => $tabela]) }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-chart-bar"></i>
                            <!-- Adiciona um badge se houver anúncios pendentes -->
                            @if (isset($pendingAdsCount) && $pendingAdsCount > 0)
                                <span class="badge badge-danger">{{ $pendingAdsCount }}</span>
                            @endif
                        </div>
                        <span class="nav-link-text ms-1">Anúncios para Avaliação</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                @if (Auth::check())
                    <a class="nav-link text-white"
                       href="{{ Auth::user()->role == 'manager'
           ? route('chat.select', ['conf' => $conf, 'tabela' => 'produto', 'product_ref' => $productRef])
           : route('chat.show', ['conf' => $conf, 'tabela' => 'produto', 'product_ref' => $productRef]) }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-comments"></i>
                            <!-- Adiciona um badge se houver novas mensagens -->
                            @if (isset($newMessagesCount) && $newMessagesCount > 0)
                                <span class="badge badge-danger">{{ $newMessagesCount }}</span>
                            @endif
                        </div>
                        <span class="nav-link-text ms-1">Mensagens</span>
                    </a>
                @endif
            </li>
                <!-- Botão para ver anúncios pedidos para publicação, visível apenas para usuários que não são managers -->
                @if ($userRole !== 'manager')
                    <li class="nav-item">
                        <a class="nav-link text-white"
                           href="{{ route('anuncios.pedidos_publicacao', ['conf' => $conf, 'tabela' => $tabela]) }}">
                            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-eye"></i>
                            </div>
                            <span class="nav-link-text ms-1">Meus Pedidos de Publicação</span>
                        </a>
                    </li>
                @endif
            <!-- Novo item para Anúncios Guardados visível para todos -->
            <li class="nav-item">
                <a class="nav-link text-white"
                   href="{{ route('anuncios.guardados', ['conf' => $conf, 'tabela' => $tabela]) }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-archive"></i>
                    </div>
                    <span class="nav-link-text ms-1">Anúncios Guardados</span>
                </a>
            </li>
            @if ($userRole !== 'manager')
                <li class="nav-item">
                    <a class="nav-link text-white"
                       href="{{ route('utilizadores', ['conf' => $conf, 'tabela' => $tabela]) }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="nav-link-text ms-1">Proposta Para Sócio</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <form action="{{ route('produtos', [$conf, $tabela]) }}" method="GET">
                    <select name="tipo_produto" id="selectTipoProduto" class="form-select text-center text-white"
                            onchange="this.form.submit()">
                        <option value="">Todos os Tipos</option>
                        @foreach($tipos as $tipo)
                            <option
                                value="{{ $tipo->descricao }}" {{ $tipoSelecionado == $tipo->descricao ? 'selected' : '' }}>
                                {{ $tipo->descricao }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </li>

            <!-- Botão Voltar -->
            <li class="nav-item mt-4">
                <a class="nav-link text-white" href="javascript:history.back()">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <span class="nav-link-text ms-1">Voltar</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Estilos personalizados -->
    <style>
        .custom-primary-bg {
            background-color: #1A73E8;
        }

        .text-white {
            color: white;
        }

        #selectAno {
            color: #333; /* Cor do texto das opções */
        }

        .nav-link:hover {
            background-color: #003366; /* Cor de fundo ao passar o mouse */
        }

        .nav-link.active {
            background-color: #1A73E8; /* Cor de fundo ativa */
        }

        .badge-danger {
            background-color: #dc3545; /* Cor de fundo do badge */
            color: white; /* Cor do texto do badge */
            font-size: 0.75rem;
            padding: 0.25em 0.4em;
            border-radius: 0.2rem;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Adicione qualquer JavaScript necessário para o seletor
        });
    </script>
</aside>
