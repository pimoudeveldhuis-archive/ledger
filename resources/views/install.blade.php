<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} | Installatie</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/ionicons.min.css">
    <link rel="stylesheet" href="/css/app.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo"><a href="{{ route('install') }}"><b>{{ config('app.name') }} installatie</b></a></div>
        <div class="login-box-body">
            @if(session('_alert'))
                <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
            @endif

            <p class="login-box-msg">Vul de onderstaande velden in om uw installatie af te ronden en in te loggen.</p>

            <form method="post" action="{{ route('do-install') }}">
                @csrf
                
                @include('helpers.input', ['id' => 'name', 'name' => 'Naam'])
                @include('helpers.input', ['id' => 'email', 'name' => 'Email adres'])
                @include('helpers.password', ['id' => 'password', 'name' => 'Wachtwoord'])
                @include('helpers.password', ['id' => 'password_check', 'name' => 'Wachtwoord (controle)'])

                <hr />

                @include('helpers.input', ['id' => 'account', 'name' => 'IBAN nr.'])
                @include('helpers.input', ['id' => 'account_name', 'name' => 'Rekening naam'])
                @include('helpers.input', ['id' => 'account_description', 'name' => 'Rekening omschrijving (optioneel)'])

                <div class="row">
                    <div class="col-xs-4 col-xs-offset-8">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Installeer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    </body>
</html>
