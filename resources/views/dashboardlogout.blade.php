@extends('layouts.app')
@php
    $showLogoutButton = true; // Define a variável para mostrar o botão de logout
@endphp
@section('content')
    <style>
        .btn-custom {
            background-color: #007bff; /* Cor de fundo personalizada */
            border: none; /* Remove a borda */
            color: white; /* Cor do texto */
            padding: 10px 20px; /* Espaçamento interno */
            font-size: 1.25rem; /* Tamanho da fonte */
            border-radius: 50px; /* Bordas arredondadas */
            transition: background-color 0.3s ease, transform 0.3s ease; /* Animação suave */
        }

        .btn-custom:hover {
            background-color: #0056b3; /* Cor de fundo ao passar o mouse */
            transform: scale(1.05); /* Aumenta ligeiramente o tamanho */
        }
    </style>
    <div class="container mt-5">
        <div class="d-flex justify-content-center align-items-center flex-column text-center">
            <video width="80%" height="auto" controls autoplay>
                <source src="{{ asset('assets/videos/recuperacao.mp4') }}" type="video/mp4">
                Seu navegador não suporta o elemento de vídeo.
            </video>
        </div>

        <h2 class="font-weight-bold text-gray-800 dark:text-gray-200 my-4 text-center">
            Voltar para Produtos
        </h2>

        <div class="d-flex justify-content-center">
            <a href="{{ route('produtos', ['conf' => 'produtos', 'tabela' => 'produto']) }}"
               class="btn btn-primary btn-lg btn-custom">
                <span class="text-gray-900 dark:text-gray-100">Voltar para produtos</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myVideo = document.querySelector("video");

            myVideo.addEventListener('ended', function () {
                // O vídeo terminou, mas não há redirecionamento nesta versão
                // Você pode adicionar lógica adicional aqui, se necessário
            });

            // Adiciona um atraso de 1 segundo antes de iniciar a tela cheia (opcional)
            setTimeout(function () {
                // Inicia automaticamente em tela cheia (opcional)
                if (myVideo.requestFullscreen) {
                    myVideo.requestFullscreen();
                } else if (myVideo.mozRequestFullScreen) {
                    myVideo.mozRequestFullScreen();
                } else if (myVideo.webkitRequestFullscreen) {
                    myVideo.webkitRequestFullscreen();
                } else if (myVideo.msRequestFullscreen) {
                    myVideo.msRequestFullscreen();
                }
            }, 1000);
        });
    </script>
@endsection
