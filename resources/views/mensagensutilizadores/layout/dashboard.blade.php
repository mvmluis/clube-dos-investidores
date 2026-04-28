@extends('mensagensutilizadores.layout.app')
@section('main-content')
    <div class="container">
        <h1>Mensagens Recebidas</h1>
        @if($mensagens->isEmpty())
            <p>Você não tem mensagens recebidas.</p>
        @else
            <ul class="list-group">
                @foreach ($mensagens as $mensagem)
                    <li class="list-group-item">
                        <strong>De:</strong> {{ $mensagem->user_id }}<br>
                        <strong>Mensagem:</strong> {{ $mensagem->mensagem }}<br>
                        <strong>Data:</strong> {{ $mensagem->created_at->format('d/m/Y H:i') }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
