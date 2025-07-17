
<nav class="navbar navbar-expand-lg bg-body-tertiary w-100 py-0">
        <div class="container h-100">
            <a class="navbar-brand" href="#">
                <img width="50" height="50" src="{{asset('/uploads/images/logos/logo.webp')}}" alt="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse h-100" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 h-100">
                
                </ul>
               @auth
                <div class="d-flex gap-3">
                    <a href="{{ route('profile.index') }}" class="main second p-2 rounded-4">
                        <svg width="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="12" cy="6" r="4" stroke="#1C274C" stroke-width="1.5"></circle> <path d="M15 20.6151C14.0907 20.8619 13.0736 21 12 21C8.13401 21 5 19.2091 5 17C5 14.7909 8.13401 13 12 13C15.866 13 19 14.7909 19 17C19 17.3453 18.9234 17.6804 18.7795 18" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                    </a>
                    <a href="{{route('logout')}}" class="btn btn-dark d-flex align-items-center rounded-4 text-white" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <span class="fs-12">تسجيل الخروج</span> 
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </a>
                </div>
                @endauth
                @guest
                    <div class="d-flex gap-3 nav-buttons" role="search">
                        <a href="{{ route('login') }}" class="btn btn-dark fs-14 rounded-4">تسجيل الدخول</a>
                    </div>
                @endguest
            </div>
        </div>
    </nav>
    <div class="navbar-space"></div>