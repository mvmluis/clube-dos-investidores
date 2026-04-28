@extends('utilizadores.layout.app')
@section('main-content')
    <style>
        .bg-white-custom {
            background-color: #ffffff;
        }

        .small-select {
            width: 150px;
        }

        .text-black-custom {
            color: #000000;
        }

        .form-label {
            color: #000000; /* Define a cor preta para os rótulos dos campos */
        }

        .table th, .table td {
            color: #000000; /* Define a cor preta para o texto da tabela */
        }

        .text-white {
            color: #ffffff; /* Define a cor do texto como branco */
        }

        /* Estilo para centralizar o botão */
        .text-center {
            text-align: center;
        }

        /* Mantenha a cor branca no fundo dos campos de entrada, mesmo quando estão em foco */
        .form-control {
            background-color: #ffffff; /* Cor de fundo branca */
            color: #000000; /* Cor do texto preta */
        }

        .form-control:focus {
            background-color: #ffffff; /* Garante que o fundo permaneça branco ao focar */
            color: #000000; /* Garante que a cor do texto permaneça preta ao focar */
            border-color: #ced4da; /* Cor da borda ao focar (opcional) */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Cor da sombra ao focar (opcional) */
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mt-4">
        <h2>Adicionar Utilizador Proposto</h2>
        <form action="{{ route('utilizadores_propostos.store') }}" method="POST">
            @csrf

            <table class="table">
                <tbody>
                <tr>
                    <td><label for="nome" class="form-label">Nome</label></td>
                    <td>
                        <input type="text"
                               class="form-control bg-white-custom text-black-custom @error('nome') is-invalid @enderror"
                               id="nome" name="nome" value="{{ old('nome') }}" required>
                        @error('nome')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>
                </tr>

                <tr>
                    <td><label for="email" class="form-label">Email</label></td>
                    <td>
                        <input type="email"
                               class="form-control bg-white-custom text-black-custom @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>
                </tr>

                <tr>
                    <td><label for="contacto" class="form-label">Contacto</label></td>
                    <td>
                        <input type="text"
                               class="form-control bg-white-custom text-black-custom @error('contacto') is-invalid @enderror"
                               id="contacto" name="contacto" value="{{ old('contacto') }}">
                        @error('contacto')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-primary">Adicionar Utilizador</button>
            </div>
        </form>
    </div>
@endsection
