@extends('anunciosguardados.layout.app')

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

        .form-group label {
            color: #000; /* Cor do texto dos labels */
        }

        .form-group img {
            max-width: 100%;
            margin-bottom: 10px;
        }

        .form-group a {
            color: #1A73E8; /* Cor dos links */
        }

        .form-group a:hover {
            text-decoration: underline;
        }

        .remove-image-btn {
            background: none;
            border: none;
            color: #ff0000;
            cursor: pointer;
            font-size: 16px;
        }

        .remove-image-checkbox {
            margin-left: 10px;
        }
    </style>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="container">
        <h1>Editar</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.update',[$conf, $tabela, $produto->ref]) }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" id="action" value="guardar">
            <!-- Adicionando a dropdown para id_tipo -->
            <div class="form-group">
                <label for="id_tipo">Tipo de Produto</label>
                <select name="id_tipo" class="form-control" required>
                    <option value="Venda de empresa / Trespasse"
                        {{ old('id_tipo', $produto->id_tipo) == 'Venda de empresa / Trespasse' ? 'selected' : '' }}>
                        Venda de empresa / Trespasse
                    </option>
                    <option value="Cedência de Exploração Industrial / Comercial"
                        {{ old('id_tipo', $produto->id_tipo) == 'Cedência de Exploração Industrial / Comercial' ? 'selected' : '' }}>
                        Cedência de Exploração Industrial / Comercial
                    </option>
                    <option value="Oportunidades de Investimento"
                        {{ old('id_tipo', $produto->id_tipo) == 'Oportunidades de Investimento' ? 'selected' : '' }}>
                        Oportunidades de Investimento
                    </option>
                    <option value="Venda de Imóveis Comerciais"
                        {{ old('id_tipo', $produto->id_tipo) == 'Venda de Imóveis Comerciais' ? 'selected' : '' }}>
                        Venda de Imóveis Comerciais
                    </option>
                    <option value="Venda de Stock"
                        {{ old('id_tipo', $produto->id_tipo) == 'Venda de Stock' ? 'selected' : '' }}>
                        Venda de Stock
                    </option>
                    <option value="Venda De Ativos"
                        {{ old('id_tipo', $produto->id_tipo) == 'Venda De Ativos' ? 'selected' : '' }}>
                        Venda De Ativos
                    </option>
                    <option value="Venda De Equipamentos"
                        {{ old('id_tipo', $produto->id_tipo) == 'Venda De Equipamentos' ? 'selected' : '' }}>
                        Venda De Equipamentos
                    </option>
                    <option value="Outros"
                        {{ old('id_tipo', $produto->id_tipo) == 'Outros' ? 'selected' : '' }}>
                        Outros
                    </option>
                </select>
            </div>

            <!-- Adicionando a dropdown para areas de actividade -->
            <div class="form-group">
                <label for="areas_actividade">Áreas de Actividade</label>
                <select name="areas_actividade" class="form-control" required>
                    <option value="Energia"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Energia' ? 'selected' : '' }}>
                        Energia
                    </option>
                    <option value="Serviços Diversos"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Serviços Diversos' ? 'selected' : '' }}>
                        Serviços Diversos
                    </option>
                    <option value="Educação"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Educação' ? 'selected' : '' }}>
                        Educação
                    </option>
                    <option value="Finanças e Seguros"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Finanças e Seguros' ? 'selected' : '' }}>
                        Finanças e Seguros
                    </option>
                    <option value="Construção Civil"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Construção Civil' ? 'selected' : '' }}>
                        Construção Civil
                    </option>
                    <option value="Restauração"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Restauração' ? 'selected' : '' }}>
                        Restauração
                    </option>
                    <option value="Turismo e Hotelaria"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Turismo e Hotelaria' ? 'selected' : '' }}>
                        Turismo e Hotelaria
                    </option>
                    <option value="Saúde"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Saúde' ? 'selected' : '' }}>
                        Saúde
                    </option>
                    <option value="Indústria Alimentar"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Indústria Alimentar' ? 'selected' : '' }}>
                        Indústria Alimentar
                    </option>
                    <option value="Indústria Transformadora"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Indústria Transformadora' ? 'selected' : '' }}>
                        Indústria Transformadora
                    </option>
                    <option value="Agricultura"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Agricultura' ? 'selected' : '' }}>
                        Agricultura
                    </option>
                    <option value="Logística"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Logística' ? 'selected' : '' }}>
                        Logística
                    </option>
                    <option value="Media"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Media' ? 'selected' : '' }}>
                        Media
                    </option>
                    <option value="Comércio"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Comércio' ? 'selected' : '' }}>
                        Comércio
                    </option>
                    <option value="Tecnologia"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Tecnologia' ? 'selected' : '' }}>
                        Tecnologia
                    </option>
                    <option value="Imobiliário"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Imobiliário' ? 'selected' : '' }}>
                        Imobiliário
                    </option>
                    <option value="Setores Diversos"
                        {{ old('areas_actividade', $produto->areas_actividade) == 'Setores Diversos' ? 'selected' : '' }}>
                        Setores Diversos
                    </option>
                </select>
            </div>

            <!-- Adicionando localização antes do título -->
            <div class="form-group">
                <label for="localizacao">Localização</label>
                <input type="text" name="localizacao" class="form-control"
                       value="{{ old('localizacao', $produto->localizacao) }}" required>
            </div>

            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $produto->titulo) }}"
                       required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" class="form-control" rows="5"
                          required>{{ old('descricao', $produto->descricao) }}</textarea>
            </div>
            <div class="form-group">
                <label for="valor">Valor (€)</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">€</span>
                    </div>
                    <input type="text" name="valor" class="form-control"
                           value="{{ old('valor', number_format($produto->valor, 2, ',', '.')) }}" required id="valor">
                </div>
            </div>
            <!-- Adicionando a dropdown para valor negociável -->
            <div class="form-group">
                <label for="valor_negociavel">Valor Negociável</label>
                <select name="valor_negociavel" class="form-control" required>
                    <option value="Negociável"
                        {{ old('valor_negociavel', $produto->valor_negociavel) == 'Negociável' ? 'selected' : '' }}>
                        Negociável
                    </option>
                    <option value="Não Negociável"
                        {{ old('valor_negociavel', $produto->valor_negociavel) == 'Não Negociável' ? 'selected' : '' }}>
                        Não Negociável
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="data">Data</label>
                <input type="date" name="data" class="form-control" value="{{ old('data', $produto->data) }}" required>
            </div>
            <div class="form-group">
                <label for="ativo">Ativo</label>
                <input type="checkbox" name="ativo" value="1"
                       class="form-check-input" {{ old('ativo', $produto->ativo) ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label for="imagem_principal">Imagem Principal</label>
                <input type="file" name="imagem_principal" class="form-control">
                @if ($produto->imagem_principal)
                    <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->titulo }}"
                         width="100">
                @endif
            </div>
            <div class="form-group">
                <label for="documentos">Documentos</label>
                <input type="file" name="documentos[]" class="form-control" multiple>
                @foreach ($documentos as $documento)
                    <div class="d-flex align-items-center mt-2">
                        <a href="{{ asset('storage/' . $documento->arquivo) }}"
                           target="_blank">{{ $documento->arquivo }}</a>
                        <input type="checkbox" name="documentos_remover[]" value="{{ $documento->id }}" class="ml-2">
                        Remover
                    </div>
                @endforeach
            </div>
            <!-- Adicionando imagens e removendo imagens -->
            <div class="form-group">
                <label for="imagens">Imagens</label>
                <input type="file" name="imagens[]" class="form-control" multiple>
                @if ($imagens->isNotEmpty())
                    @foreach ($imagens as $imagem)
                        <div class="d-flex align-items-center mt-2">
                            <img src="{{ asset('storage/' . $imagem->caminho) }}" alt="Imagem {{ $loop->iteration }}"
                                 width="100">
                            <input type="checkbox" name="imagens_remover[]" value="{{ $imagem->id }}" class="remove-image-checkbox ml-2">
                            <label class="ml-2">Remover</label>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Botão centralizado -->
            <div class="form-group centered-button">
                <button type="submit" class="btn btn-primary " onclick="document.getElementById('action').value = 'pedir_publicacao';">Pedir publicação</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            // Remover o botão e a imagem correspondente da DOM
            document.querySelectorAll('.remove-image-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    // Remove o botão e a imagem correspondente da DOM
                    this.parentElement.remove();

                    // Opcional: Remover o campo oculto correspondente para garantir que o backend saiba que a imagem deve ser removida
                    const inputHidden = document.querySelector(`input[name="imagens_remover[]"][value="${id}"]`);
                    if (inputHidden) {
                        inputHidden.remove();
                    }
                });
            });
        });
    </script>

@endsection
