<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link bg-gray-dark">
        <img src="{{ asset('img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-square" style="opacity: .8">
        <span class="brand-text font-weight-light">ITL Live Web</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('img/avatar5.png') }}" class="img-circle elevation-2" alt="User Image">
            </div> 
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->user_fullname }}</a>
            </div>
        </div>

        @include('inc.menu')
        
    </div>
    <!-- /.sidebar -->
</aside>