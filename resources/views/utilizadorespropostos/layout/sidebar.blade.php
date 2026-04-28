<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark d-lg-block d-xl-block "
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="https://www.consultingcast.pt/" target="_blank">
            <img src="{{ asset('assets/img/clubeinvestidores.png') }}" class="navbar-brand-img img-fluid" style="max-width: 190px; max-height: 300px;" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white" style="font-size: 13px;"></span>
        </a>
    </div>

    <hr class="horizontal light mt-7 mb-3">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item mt-3">
                <a class="nav-link text-white" href="javascript:history.back()">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <span class="nav-link-text ms-1">Voltar</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-white" href="{{ route('utilizadores_ativos') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users opacity-10"></i> <!-- Ícone de utilizador -->
                    </div>
                    <span class="nav-link-text ms-1">Utilizadores Ativos</span>
                </a>
            </li>
        </ul>
    </div>
    <style>
        .custom-primary-bg {
            background-color: #1A73E8;
        }

        .text-white {
            color: white;
        }

        #selectMes option {
            color: #333; /* Cor do texto das opções dos meses */
        }

        #selectAno option {
            color: #333; /* Cor do texto das opções dos anos */
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Adicionar evento change ao seletor de ano
            document.getElementById('selectAno').addEventListener('change', function (event) {
                // Obter o ano selecionado
                var anoSelecionado = this.value;

                // Atualizar o valor do campo de ano oculto no formulário de finanças
                document.getElementById('anoSelecionadoFinancas').value = anoSelecionado;
            });

            // Adicionar evento submit ao formulário de finanças
            document.getElementById('formFinancas').addEventListener('submit', function (event) {
                // Obter o ano selecionado
                var anoSelecionado = document.getElementById('selectAno').value;

                // Atualizar o valor do campo de ano oculto no formulário de finanças
                document.getElementById('anoSelecionadoFinancas').value = anoSelecionado;
            });

        });
    </script>
</aside>
