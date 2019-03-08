<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name') }} | @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/font-awesome.min.css">
        <link rel="stylesheet" href="/css/ionicons.min.css">
        <link rel="stylesheet" href="/css/select2.min.css">

        <link rel="stylesheet" href="/css/app.css">
        <link rel="stylesheet" href="/css/skin-blue.min.css">

        <link rel="stylesheet" href="/css/style.css">

        @yield('css')

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic|Inconsolata:400,700">
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('home') }}" class="logo">
                    <span class="logo-mini"></span>
                    <span class="logo-lg"><b>{{ config('app.name') }}</b></span>
                </a>

                <nav class="navbar navbar-static-top" role="navigation">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"><span class="sr-only">Toggle navigation</span></a>

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- <li class="dropdown notifications-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell-o"></i><span class="label label-warning">10</span></a>

                                <ul class="dropdown-menu">
                                    <li class="header">You have 10 notifications</li>
                                    
                                    <li>
                                        <ul class="menu">
                                            <li><a href="#"><i class="fa fa-users text-aqua"></i> 5 new members joined today</a></li>
                                            <li><a href="#"><i class="fa fa-users text-aqua"></i> 5 new members joined today</a></li>
                                            <li><a href="#"><i class="fa fa-users text-aqua"></i> 5 new members joined today</a></li>
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="#">View all</a></li>
                                </ul>
                            </li> -->
                        </ul>
                    </div>
                </nav>
            </header>

            <aside class="main-sidebar">
                <section class="sidebar">
                    <!-- <div class="user-panel">
                        <div class="pull-left image"><img src="dist/img/user2-160x160.jpg" class="img-circle" alt="{{ Auth::user()->name }}" /></div>
                        <div class="pull-left info"><p>{{ Auth::user()->name }}</p><a href="#"><i class="fa fa-circle text-success"></i> Online</a></div>
                    </div> -->

                    <ul class="sidebar-menu" data-widget="tree">
                        <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> <span>Dashboard</span></a></li>
                        <li><a href="{{ route('accounts') }}"><i class="fa fa-credit-card"></i> <span>Rekeningen</span></a></li>
                        <li><a href="{{ route('budgets') }}"><i class="fa fa-money"></i> <span>Budgetten</span></a></li>
                        <li><a href="{{ route('categories') }}"><i class="fa fa-list"></i> <span>Categorieën</span></a></li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-exchange"></i> <span>Transacties</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ route('transactions') }}"><i class="fa fa-circle-o"></i> Overzicht</a></li>
                                <li><a href="{{ route('duplicates') }}"><i class="fa fa-circle-o"></i> Dubbele transacties</a></li>
                                <li><a href="{{ route('import-create') }}"><i class="fa fa-circle-o"></i> Importeren</a></li>
                                <!-- <li><a href="{{ route('home') }}"><i class="fa fa-circle-o"></i> Exporteren</a></li> -->
                            </ul>
                        </li>

                        <!-- <li><a href="{{ route('home') }}"><i class="fa fa-tags"></i> <span>Tags</span></a></li> -->
                        <li><a href="{{ route('do-logout') }}"><i class="fa fa-lock"></i> <span>Uitloggen</span></a></li>
                    </ul>
                </section>
            </aside>

            <div class="content-wrapper">
                <section class="content-header">
                    <h1>
                        @yield('title')
                        <small>@yield('description')</small>
                    </h1>
                </section>

                <section class="content container-fluid">
                    @yield('content')
                </section>
            </div>

            <footer class="main-footer">
                Developed by <a href="https://pim.odvh.nl/" target="_blank">Pim Oude Veldhuis</a>, released under MIT License on <a href="#">Github</a>.
                <div class="pull-right hidden-xs">Versie 0.0.1</div>
            </footer>
        </div>

        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/adminlte.min.js"></script>
        <script src="/js/select2.min.js"></script>
        
        @yield('js')
    </body>
</html>