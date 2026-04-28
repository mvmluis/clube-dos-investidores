<div class="main-content position-relative max-height-vh-100 h-100">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
         data-scroll="true">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                    </li>
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Produtos</li>
                </ol>
                <h6 class="font-weight-bolder mb-0">Favoritos</h6>
            </nav>
            <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Type here...</label>
                        <input type="text" class="form-control">
                    </div>
                </div>
                <ul class="navbar-nav  justify-content-end">
                    <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                            <div class="sidenav-toggler-inner">
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Carousel -->
    <div id="adCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('assets/img/banners/banner site-04.png') }}" class="d-block w-100" alt="Ad 1">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-05.png') }}" class="d-block w-100" alt="Ad 2">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-06.png') }}" class="d-block w-100" alt="Ad 3">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-07.png') }}" class="d-block w-100" alt="Ad 4">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-08.png') }}" class="d-block w-100" alt="Ad 5">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-09.png') }}" class="d-block w-100" alt="Ad 6">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-10.png') }}" class="d-block w-100" alt="Ad 7">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('assets/img/banners/banner site-11.png') }}" class="d-block w-100" alt="Ad 8">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#adCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#adCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- End Carousel -->

    <!-- Seu JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var adCarousel = document.getElementById('adCarousel');
            var lastScrollPosition = window.scrollY;

            window.addEventListener('scroll', function () {
                var scrollPosition = window.scrollY;
                var scrollDifference = scrollPosition - lastScrollPosition;

                adCarousel.style.transform = 'translateY(' + scrollDifference + 'px)';
                lastScrollPosition = scrollPosition;
            });
        });
    </script>
</div>
