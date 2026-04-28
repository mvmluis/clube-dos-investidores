@extends('utilizadorespropostos.layout.app')
@section('main-content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .bg-light-custom {
            background-color: #f8f9fa;
        }

        .text-dark-custom {
            color: #343a40;
        }

        .form-label {
            color: #495057; /* Cor mais suave para os rótulos dos campos */
        }

        .table-custom {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%; /* Garante que a tabela ocupa toda a largura disponível */
            border-collapse: collapse; /* Remove o espaço entre bordas das células */
        }

        .table-custom th, .table-custom td {
            color: #495057;
            padding: 12px 15px;
            text-align: left; /* Alinha o texto à esquerda */
        }

        .table-custom thead {
            background-color: #007bff;
            color: #ffffff !important; /* Garante que a cor dos títulos é branca */
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #f1f1f1;
        }

        /* Remover o efeito de hover */
        .table-custom tbody tr:hover {
            background-color: #ffffff; /* Mantém a cor de fundo ao passar o mouse */
        }

        .alert-custom {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-success-custom {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-info-custom {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        .btn-primary-custom {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            padding: 10px 20px;
            color: #ffffff; /* Cor do texto do botão */
            font-size: 16px;
        }

        .btn-primary-custom:hover {
            background-color: #0056b3;
            border-color: #004085;
            color: #ffffff; /* Cor do texto do botão no hover */
        }

        /* Estilo para centralizar o botão */
        .text-center {
            text-align: center;
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-custom">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mt-4">
        <h2 class="text-dark-custom">Utilizadores Propostos</h2>

        <!-- Lista de utilizadores propostos -->
        <div class="mt-4">
            @if($proposedUsers->isNotEmpty())
                <form action="{{ route('utilizadores_propostos.updateEstados') }}" method="POST">
                    @csrf
                    <table class="table table-custom">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Sugerido Por</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($proposedUsers as $user)
                            <tr>
                                <td>{{ $user->nome }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->contacto }}</td>
                                <td>
                                    <select name="estado[{{ $user->id }}]" class="form-select">
                                        <option value="em_avaliacao" {{ $user->estado == 'em_avaliacao' ? 'selected' : '' }}>Em Avaliação</option>
                                        <option value="aceite" {{ $user->estado == 'aceite' ? 'selected' : '' }}>Aceite</option>
                                        <option value="rejeitado" {{ $user->estado == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                                    </select>
                                </td>
                                <td>
                                    @if($user->sugerido_por_nome)
                                        <div>
                                            <p><strong>Nome:</strong> {{ $user->sugerido_por_nome }}</p>
                                            <p><strong>Email:</strong> {{ $user->sugerido_por_email }}</p>
                                            <p><strong>Contacto:</strong> {{ $user->sugerido_por_contacto }}</p>
                                        </div>
                                    @else
                                        Nenhum usuário sugeriu
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3 text-center">
                        <button type="submit" class="btn btn-primary">Atualizar Estados</button>
                    </div>
                </form>
            @else
                <div class="alert alert-info alert-info-custom">Nenhum utilizador proposto encontrado.</div>
            @endif
        </div>
    </div>
@endsection
