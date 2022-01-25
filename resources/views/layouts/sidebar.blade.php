<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('home') }}" class="brand-link">
        <img src="{{ asset('/admin-lte/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AI 2021 VN</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('/admin-lte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-header">Crawler Website</li>
                <li class="nav-item">
                    <a href=" {{ url('list-scraper') }}" class="nav-link">
                        <i class="nav-icon fas fa-list-ol"></i>
                        <p> List Scraper </p>
                    </a>

                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-water"></i>
                        <p>
                            Scraper
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href=" {{ url('view-scraper') }}" class="nav-link">
                                <i class="nav-icon fas fa-wrench"></i>
                                <p>Scraper Setup </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('list-templates') }}" class="nav-link">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <p>List Template</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('new-template/'.env('STORE_WOO_ID')) }}" class="nav-link">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>New Template Woo</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('new-template/'.env('STORE_SHOPBASE_ID')) }}" class="nav-link">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>New Template Shopbase</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-link"></i>
                        <p>
                            Connect
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('list-stores') }}" class="nav-link">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <p>List Stores</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('connect-woo') }}" class="nav-link">
                                <i class="nav-icon fab fa-wordpress"></i>
                                <p>Woo Commerce</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fab fa-stripe-s nav-icon"></i>
                                <p>Shop Base</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('list-paypal') }}" class="nav-link">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <p>List Paypal</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
