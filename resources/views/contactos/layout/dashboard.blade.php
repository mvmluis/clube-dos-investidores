@extends('contactos.layout.app')

@section('main-content')
    <style>
        .concluido-col {
            width: 120px; /* Ajuste conforme necessário */
            text-align: center; /* Centraliza o texto na coluna */
        }
        table {
            width: 100%; /* Garante que a tabela ocupe toda a largura disponível */
            table-layout: fixed; /* Faz com que as colunas respeitem a largura fixa definida */
        }
        th, td {
            text-align: center; /* Centraliza o texto em todas as células da tabela */
            vertical-align: middle; /* Centraliza verticalmente o texto nas células */
        }
        /* Força a cor preta para o texto das células de dados */
        td {
            color: black !important;
        }
    </style>
    <div class="container">
        <h2>Contactos</h2>
        @if($contactos->isEmpty())
            <p>Não há contactos disponíveis.</p>
        @else
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Ref</th>
                    <th>Usuário</th>
                    <th>Data</th>
                    <th class="concluido-col">Concluído</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($contactos as $contacto)
                    <tr>
                        <td>{{ $contacto->produto_titulo }}</td>
                        <td>{{ $contacto->user_name }}</td>
                        <td>{{ $contacto->data ? $contacto->data->format('d/m/Y') : 'N/A' }}</td>
                        <td class="concluido-col">
                            <form action="{{ route('contactos.update', $contacto->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="concluido" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="0" {{ $contacto->concluido == 0 ? 'selected' : '' }}>Não</option>
                                    <option value="1" {{ $contacto->concluido == 1 ? 'selected' : '' }}>Sim</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
