@extends('utilizadoresativos.layout.app')

@section('main-content')
    <style>
        .table-custom {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            border-collapse: collapse;
        }

        .table-custom th, .table-custom td {
            padding: 12px 15px;
            text-align: left;
        }

        .table-custom thead {
            background-color: #007bff;
            color: #ffffff;
        }

        /* Títulos em branco */
        .table-custom th {
            color: #ffffff; /* Define os títulos como brancos */
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #f1f1f1;
        }

        .table-custom td {
            color: #000000; /* Cor do texto das células da tabela */
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85em;
            transition: background-color 0.3s, transform 0.3s;
        }

        .status-active {
            background-color: #28a745; /* Verde para Ativo */
        }

        .status-inactive {
            background-color: #dc3545; /* Vermelho para Inativo */
        }

        .status-badge:hover {
            transform: scale(1.1); /* Animação ao passar o mouse */
        }
    </style>
    <div class="container mt-4">
        <h2 class="text-dark-custom">Utilizadores Ativos</h2>

        @if($activeUsers->isNotEmpty())
            <table class="table table-custom">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Contacto</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($activeUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->contacto }}</td>
                        <td>
                            <span
                                class="status-badge {{ $user->status == 'ativo' ? 'status-active' : 'status-inactive' }}">
                                {{ $user->status == 'ativo' ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Nenhum utilizador ativo encontrado.</div>
        @endif
    </div>
@endsection
