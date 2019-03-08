@extends('template')

@section('title', 'Rekeningen')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            @if(Auth::user()->accounts()->count() > 0)
                <div class="box">
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Omschrijving</th>
                                    <th>IBAN nr.</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(Auth::user()->accounts()->orderBy('name', 'ASC')->limit(400)->get() AS $account)
                                    <tr>
                                        <td>{{ $account->name }}</td>
                                        <td>{{ $account->description }}</td>
                                        <td>{{ $account->account }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('account-edit', ['id' => $account->id]) }}" class="btn btn-xs btn-warning">Wijzigen</a>
                                            <a href="{{ route('account-do-delete', ['id' => $account->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('U staat op het punt om de rekening {{ $account->name }} te verwijderen. Weet u dit zeker?')">Verwijder</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">Geen rekeningen gevonden. <a href="{{ route('account-create') }}">Klik hier</a> om je eerste rekening te maken.</div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Uitleg</h3>
                </div>

                <div class="box-body">
                    <p>TODO</p>
                    <p class="text-center"><a class="btn btn-success" href="{{ route('account-create') }}">Nieuwe rekening</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection