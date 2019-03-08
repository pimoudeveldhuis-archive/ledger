@extends('template')

@section('title', 'Rekeningen')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            @if(isset($account))
                <form method="POST" action="{{ route('account-do-edit', ['id' => $account->id]) }}">
            @else
                <form method="POST" action="{{ route('account-do-create') }}">
            @endif

                @csrf

                <div class="box">
                    <div class="box-body">
                        @if(isset($account))
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam', 'value' => $account->name])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving', 'value' => $account->description])
                        @else
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam'])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving'])
                            @include('helpers.input', ['id' => 'account', 'name' => 'Rekening nr.'])
                        @endif
                    </div>
                    <div class="box-footer">
                        @if(isset($account))
                            <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                        @else
                            <button name="submit" type="submit" value="create" class="btn btn-success">Aanmaken</button>
                        @endif

                        <a href="{{ route('accounts') }}" class="btn btn-info">Annuleren</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Uitleg</h3>
                </div>

                <div class="box-body">
                    <p>TODO</p>
                </div>
            </div>
        </div>
    </div>
@endsection