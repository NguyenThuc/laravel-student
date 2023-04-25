<ul class="nav flex-column side-bar">
    <li class="nav-item">
        <p class="nav-link menu-agency-name">{{data_get(Auth::user('seller'), 'agency.name', '')}}
            <i  aria-hidden="true"></i>
        </p>
        <a class="nav-link active" href="/">ダッシュボード </a>
    </li>
    <li class="nav-item">
        <p class="nav-link">教育機関管理
            <i  aria-hidden="true"></i>
        </p>
        <a class="nav-link active" href="/educational_institutions/create">教育機関新規登録</a>
        <a class="nav-link active" href="/educational_institutions/">教育機関一覧</a>
    </li>
    <li class="nav-item">
        <p class="nav-link">教育機関スタッフ管理
            <i  aria-hidden="true"></i>
        </p>
        <a class="nav-link active" href="/">教育機関スタッフ新規登録</a>
        <a class="nav-link active" href="/educational_staffs">教育機関スタッフ一覧</a>
    </li>
    <li class="nav-item">
        <p class="nav-link">販売店スタッフ管理
            <i  aria-hidden="true"></i>
        </p>
        @can('create', Auth::user())
        <a class="nav-link active" href="{{ route('seller.create') }}">販売店スタッフ新規登録</a>
        @endcan
        <a class="nav-link active" href="{{ route('seller.list') }}">販売店スタッフ一覧</a>
    </li>
</ul>
