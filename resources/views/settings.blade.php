@extends('template')

@section('title', 'Instellingen')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <form method="POST" action="{{ route('do-settings', ['key' => 'email']) }}">
                    @csrf

                    <div class="box-header with-border">
                        <h3 class="box-title">E-mail adres wijzigen</h3>
                    </div>

                    <div class="box-body">
                        @include('helpers.input', ['id' => 'email', 'name' => 'Email adres', 'value' => Auth::user()->email])
                        @include('helpers.password', ['id' => 'password', 'name' => 'Wachtwoord (ter controle)'])
                    </div>

                    <div class="box-footer">
                        <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                        <a href="{{ route('settings') }}" class="btn btn-info">Annuleren</a>
                    </div>
                </form>
            </div>

            <div class="box">
                <form method="POST" action="{{ route('do-settings', ['key' => 'recovery_reset']) }}">
                    @csrf

                    <div class="box-header with-border">
                        <h3 class="box-title">Herstelcode resetten</h3>
                    </div>

                    <div class="box-body">
                        @include('helpers.password', ['id' => 'password', 'name' => 'Wachtwoord (ter controle)'])
                    </div>

                    <div class="box-footer">
                        <button name="submit" type="submit" value="edit" class="btn btn-success">Reset</button>
                        <a href="{{ route('settings') }}" class="btn btn-info">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="box">
                <form method="POST" action="{{ route('do-settings', ['key' => 'password']) }}">
                    @csrf

                    <div class="box-header with-border">
                        <h3 class="box-title">Wachtwoord wijzigen</h3>
                    </div>

                    <div class="box-body">
                        @include('helpers.password', ['id' => 'pwupdate_password_old', 'name' => 'Huidige wachtwoord'])
                        @include('helpers.password', ['id' => 'pwupdate_password_new', 'name' => 'Nieuwe Wachtwoord'])
                        @include('helpers.password', ['id' => 'pwupdate_password_new_check', 'name' => 'Nieuwe Wachtwoord (controle)'])
                    </div>

                    <div class="box-footer">
                        <button name="pwupdate_submit" type="submit" value="edit" class="btn btn-success" dusk="pwupdate-button">Wijzigen</button>
                        <a href="{{ route('settings') }}" class="btn btn-info">Annuleren</a>
                    </div>
                </form>
            </div>

            <div class="box">
                <form method="POST" action="{{ route('do-settings', ['key' => 'password_reset']) }}">
                    @csrf

                    <div class="box-header with-border">
                        <h3 class="box-title">Wachtwoord reset</h3>
                    </div>

                    <div class="box-body">
                        @include('helpers.password', ['id' => 'recovery', 'name' => 'Herstelcode'])
                        @include('helpers.password', ['id' => 'password_new', 'name' => 'Nieuwe Wachtwoord'])
                        @include('helpers.password', ['id' => 'password_new_check', 'name' => 'Nieuwe Wachtwoord (controle)'])
                    </div>

                    <div class="box-footer">
                        <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                        <a href="{{ route('settings') }}" class="btn btn-info">Annuleren</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection