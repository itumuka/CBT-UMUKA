<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="fa fa-user"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a href="#" class="dropdown-item"
           onclick="return (typeof adminConfirmLogout === 'function' ? adminConfirmLogout(event) : (event.preventDefault(), document.getElementById('logout-form').submit(), false));">
            <i class="fa fa-door-open text-secondary mr-2"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</li>
