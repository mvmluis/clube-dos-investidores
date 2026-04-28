@extends('novo_dashboard.layout.app')

@section('main-content')
    <style>
        .custom-blue-btn {
            background-color: #1A73E8;
            color: #fff; /* Texto branco nos botões */
        }

        .custom-blue-btn:hover {
            background-color: #1A73E8;
            color: #fff;
        }

        /* Estilos adicionais para centralizar o botão */
        .centered-button {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Garantir que o texto nos campos do formulário seja preto */
        .form-control {
            background-color: white;
            color: #000; /* Cor do texto nos campos do formulário */
            border: 1px solid #ced4da; /* Borda padrão do Bootstrap */
        }

        .form-control:focus {
            background-color: white; /* Mantém o fundo branco ao focar */
            color: #000; /* Mantém o texto preto ao focar */
            border-color: #80bdff; /* Cor da borda ao focar */
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25); /* Sombra ao focar */
        }

        .form-check-input {
            accent-color: #000; /* Cor do checkbox para manter a visibilidade */
        }

        .btn-primary {
            color: #fff; /* Texto branco nos botões primários */
        }

        /* Estilos adicionais, se necessário */
        .form-group label {
            color: #000; /* Cor do texto dos labels */
        }

        /* Adiciona espaço entre os botões */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 400px; /* Espaçamento entre os botões */
        }
    </style>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('produtos.store', [$conf, $tabela]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" id="action" value="guardar">
            <!-- Adicionando a dropdown para id_tipo -->
            <div class="form-group">
                <label for="id_tipo">Tipo de Produto</label>
                <select name="id_tipo" class="form-control" required>
                    <option value="Venda de empresa / Trespasse">Venda de empresa / Trespasse</option>
                    <option value="Cedência de Exploração Industrial / Comercial">Cedência de Exploração Industrial / Comercial</option>
                    <option value="Oportunidades de Investimento">Oportunidades de Investimento</option>
                    <option value="Venda de Imóveis Comerciais">Venda de Imóveis Comerciais</option>
                    <option value="Venda de Stock">Venda de Stock</option>
                    <option value="Venda De Ativos">Venda De Ativos</option>
                    <option value="Venda De Equipamentos">Venda De Equipamentos</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>

            <!-- Adicionando a dropdown para áreas de atividade -->
            <div class="form-group">
                <label for="areas_actividade">Áreas de Actividade</label>
                <select name="areas_actividade" class="form-control" required>
                    <option value="Energia">Energia</option>
                    <option value="Serviços Diversos">Serviços Diversos</option>
                    <option value="Educação">Educação</option>
                    <option value="Finanças e Seguros">Finanças e Seguros</option>
                    <option value="Construção Civil">Construção Civil</option>
                    <option value="Restauração">Restauração</option>
                    <option value="Turismo e Hotelaria">Turismo e Hotelaria</option>
                    <option value="Saúde">Saúde</option>
                    <option value="Indústria Alimentar">Indústria Alimentar</option>
                    <option value="Indústria Transformadora">Indústria Transformadora</option>
                    <option value="Agricultura">Agricultura</option>
                    <option value="Logística">Logística</option>
                    <option value="Media">Media</option>
                    <option value="Comércio">Comércio</option>
                    <option value="Tecnologia">Tecnologia</option>
                    <option value="Imobiliário">Imobiliário</option>
                    <option value="Setores Diversos">Setores Diversos</option>
                </select>
            </div>

            <!-- Adicionando localização antes do título -->
            <div class="form-group">
                <label for="localizacao">Localização</label>
                <input type="text" name="localizacao" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descricao">Apresentação da oportunidade</label>
                <textarea name="descricao" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="valor">Valor Solicitado (€)</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">€</span>
                    </div>
                    <input type="text" name="valor" class="form-control" required id="valor" value="0,00 €">
                </div>
            </div>

            <!-- Adicionando a dropdown para valor negociável -->
            <div class="form-group">
                <label for="valor_negociavel">Valor Negociável</label>
                <select name="valor_negociavel" class="form-control" required>
                    <option value="Negociável">Negociável</option>
                    <option value="Não Negociável">Não Negociável</option>
                </select>
            </div>

            <div class="form-group">
                <label for="data">Data</label>
                <input type="date" name="data" class="form-control" required id="data">
            </div>
            <div class="form-group">
                <label for="ativo">Ativo</label>
                <input type="checkbox" name="ativo" value="1" class="form-check-input" checked>
            </div>
            <div class="form-group">
                <label for="imagem_principal">Imagem Principal</label>
                <input type="file" name="imagem_principal" class="form-control">
            </div>
            <div class="form-group">
                <label for="documentos">Documentos</label>
                <input type="file" name="documentos[]" class="form-control" multiple>
            </div>

            <!-- Adicionando múltiplas imagens -->
            <div class="form-group">
                <label for="imagens">Imagens Adicionais</label>
                <input type="file" name="imagens[]" class="form-control" multiple>
            </div>

            <!-- Botões lado a lado -->
            @if($userRole == 'manager')
                <div class="form-group button-group">
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('action').value = 'publicar';">Publicar</button>
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('action').value = 'guardar';">Guardar</button>
                </div>
            @else
                <div class="form-group button-group">
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('action').value = 'guardar';">Guardar</button>
                <div class="form-group button-group">
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('action').value = 'pedir_publicacao';">Pedir publicação</button>
                </div>
            @endif
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dataInput = document.getElementById('data');
            var today = new Date().toISOString().split('T')[0];
            dataInput.value = today;

            var valorInput = document.getElementById('valor');

            // Função para formatar o valor com duas casas decimais
            function formatValor(value) {
                // Remove todos os caracteres não numéricos e substitui vírgulas por pontos
                value = value.replace(/[^0-9.,]/g, '').replace(',', '.');
                // Converte para número e formata com duas casas decimais
                var numberValue = parseFloat(value);
                if (isNaN(numberValue)) numberValue = 0;
                // Formata para o estilo pt-PT com separadores de milhar e casas decimais
                return numberValue.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace(/\s/g, '.') + ' €'; // Substitui os espaços por pontos
            }

            // Função para formatar o valor quando o campo perde o foco
            function onBlur() {
                this.value = formatValor(this.value);
            }

            valorInput.addEventListener('input', function () {
                // Remove caracteres não numéricos durante a digitação
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            valorInput.addEventListener('blur', onBlur);

            // Formata o valor ao carregar a página
            valorInput.value = formatValor(valorInput.value || '0,00 €');
        });
    </script>
@endsection
