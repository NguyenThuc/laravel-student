<nav class="navbar navbar-expand-lg mb-4 header" style="background: #f8f8f8;">
    <div class="container-fluid">

        <div class="collapse navbar-collapse justify-content-end" id="mob-navbar">
            @auth
            @if(!isset($disableAuth) || $disableAuth === true)
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle txt-ellipsis"
                    style="max-width: 220px"
                    href="#"
                    id="navbarDropdown"
                    role="button"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fa fa-user-o" aria-hidden="true"></i> 
                    {{ auth('seller')->user()->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('my-page') }}">登録情報確認</a>
                        <a class="dropdown-item" href="{{ route('logout') }}">ログアウト</a>
                    </div>
                </li>
            </ul>
            @endif
            @endauth
            
        </div>
    </div>
</nav>
