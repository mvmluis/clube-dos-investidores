@extends('chatselecao.layout.app')

@section('main-content')
    <div class="container">
        <h1 class="text-black">Selecione o Usuário para Ver o Chat</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($noDataMessage))
            <div class="alert alert-info">
                {{ $noDataMessage }}
            </div>
        @else
            <table class="table table-striped">
                <thead class="text-black">
                <tr>
                    <th>Título do Produto</th>
                    <th>Nome do Usuário</th>
                    <th>Ações</th>
                    <th>Notificações</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($productsWithTitles as $productRef => $details)
                    <tr class="text-black">
                        <td>{{ $details['titulo'] }}</td>
                        <td>
                            @php
                                $productUsers = $users->whereIn('id', $details['user_ids']);
                            @endphp
                            @foreach ($productUsers as $user)
                                <p>{{ $user->name }}</p>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('chat.show', ['conf' => $conf, 'tabela' => $tabela, 'product_ref' => $productRef]) }}" class="btn btn-primary">Ver Chat</a>
                        </td>
                        <td>
                            @if ($details['unread_count'] > 0)
                                <span class="badge bg-danger text-black">{{ $details['unread_count'] }} novas mensagens</span>
                            @else
                                <span class="badge bg-success text-black">Nenhuma nova mensagem</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
