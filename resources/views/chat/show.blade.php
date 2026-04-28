@extends('novo_dashboard.layout.app')

@section('main-content')
    <div class="container">
        <h1>Chat para o Produto: {{ $tituloProduto }}</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(Auth::user()->role === 'manager' && isset($pendingAdsCount) && $pendingAdsCount > 0)
            <p>Há {{ $pendingAdsCount }} anúncios pendentes.</p>
        @endif

        @if(isset($newMessagesCount) && $newMessagesCount > 0)
            <p>Você tem {{ $newMessagesCount }} novas mensagens.</p>
        @endif

        <!-- Caixa de Chat -->
        <div class="chat-box" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; background-color: #f9f9f9;">
            @if(isset($noMessagesMessage))
                <div class="alert alert-info">
                    {{ $noMessagesMessage }}
                </div>
            @else
                @foreach ($messages as $message)
                    <div class="chat-message {{ $message->user_id == Auth::id() ? 'chat-message-admin' : 'chat-message-user' }}"
                         style="margin-bottom: 10px; padding: 10px; border-radius: 5px; background-color: {{ $message->user_id == Auth::id() ? '#d1ecf1' : '#ffffff' }}; border: 1px solid {{ $message->user_id == Auth::id() ? '#bee5eb' : '#ddd' }};">
                        <strong>{{ $message->remetente_nome ?? 'Desconhecido' }}:</strong>
                        <p>{{ $message->mensagem }}</p>
                        <small style="color: #6c757d;">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Formulário de Envio de Mensagem -->
        <form action="{{ route('chat.send', ['conf' => $conf, 'tabela' => $tabela, 'product_ref' => $product_ref]) }}" method="POST" style="margin-top: 10px;">
            @csrf
            <input type="hidden" name="produto_ref" value="{{ $product_ref }}">
            <input type="hidden" name="conf" value="{{ $conf }}">
            <input type="hidden" name="tabela" value="{{ $tabela }}">

            <div class="form-group">
                <label for="mensagem">Sua Mensagem:</label>
                <textarea name="mensagem" id="mensagem" class="form-control" rows="4" required style="background-color: #ffffff; color: #000000;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-2">Enviar Mensagem</button>
        </form>
    </div>
@endsection
