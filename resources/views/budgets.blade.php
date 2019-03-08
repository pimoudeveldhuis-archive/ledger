@extends('template')

@section('title', 'Budgetten')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            @if(Auth::user()->budgets()->count() > 0)
                <div class="box">
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Omschrijving</th>
                                    <th>Bedrag (standaard)</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(Auth::user()->budgets()->orderBy('name', 'ASC')->limit(400)->get() AS $budget)
                                    <tr>
                                        <td>
                                            @if($budget->icon !== null)
                                                <i class="fa fa-{{ $budget->icon }}"></i>&nbsp;{{ $budget->name }}
                                            @else
                                                {{ $budget->name }}
                                            @endif
                                        </td>
                                        <td>{{ $budget->description }}</td>
                                        <td>{{ \CurrencyHelper::readable($budget->currency->code, $budget->default_amount) }}</td>
                                        <td class="text-right">
                                            @if($budget->transactions()->count() > 0)
                                                <a href="{{ route('budget', ['id' => $budget->id]) }}" class="btn btn-xs btn-success">Bekijken</a>
                                            @endif
                                            
                                            <a href="{{ route('budget-do-run', ['id' => $budget->id]) }}" class="btn btn-xs btn-info">Regels toepassen</a>
                                            <a href="{{ route('budget-edit', ['id' => $budget->id]) }}" class="btn btn-xs btn-warning">Wijzigen</a>
                                            <a href="{{ route('budget-do-delete', ['id' => $budget->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('U staat op het punt om het budget {{ $budget->name }} te verwijderen. Weet u dit zeker?')">Verwijder</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">Geen budgetten gevonden. <a href="{{ route('budget-create') }}">Klik hier</a> om je eerste budget te maken.</div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Uitleg</h3>
                </div>

                <div class="box-body">
                    <p>TODO</p>
                    <p class="text-center"><a class="btn btn-success" href="{{ route('budget-create') }}">Nieuw budget</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection