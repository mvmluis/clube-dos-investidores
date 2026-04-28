@extends('chat.layout.app')

@section('main-content')
    <!-- Em resources/views/chat/index.blade.php -->

    <div class="chat-container">
        <!-- Exibição das mensagens -->
        <div class="messages">
            @foreach ($chats as $chat)
                <div class="message">
                    <div class="message-header">
                        <strong>User {{ $chat->user_id }}</strong>
                        <span class="message-time">{{ $chat->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="message-body">{{ $chat->mensagem }}</p>
                </div>
            @endforeach
        </div>

        <!-- Formulário para enviar mensagem -->
        <form action="{{ route('chat.enviar', ['conf' => $conf, 'tabela' => $tabela, 'anuncioRef' => $anuncio->ref]) }}" method="POST" class="message-form">
            @csrf
            <textarea name="mensagem" required placeholder="Digite sua mensagem..." rows="3"></textarea>
            <button type="submit" class="btn-send">Enviar Mensagem</button>
        </form>
    </div>

    <style>
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 600px;
            overflow: hidden;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .message {
            background-color: #ffffff;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .message-time {
            font-size: 0.8em;
            color: #888;
        }

        .message-body {
            font-size: 1em;
            color: #333;
        }

        .message-form {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
        }

        .message-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: none;
            font-size: 1em;
            margin-bottom: 10px;
        }

        .btn-send {
            background-color: #1A73E8;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .btn-send:hover {
            background-color: #1556b0;
        }
    </style>
@endsection
