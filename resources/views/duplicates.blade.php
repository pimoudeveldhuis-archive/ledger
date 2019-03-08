@extends('template')

@section('title', 'Dubbele transacties')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    @if(Auth::user()->transactions()->where('duplicate', true)->count() > 0)
        <div class="box">
            <div class="box-body no-padding">
                <div class="text-center" style="margin: 20px;">
                    <a href="{{ route('transaction-do-duplicate-delete-all') }}" class="btn btn-danger" onclick="return confirm('U staat op het punt om alle dubbele transacties permanent te verwijderen. Weet u dit zeker?')">Verwijder alle dubbele transacties</a>
                </div>

                @foreach(Auth::user()->transactions()->where('duplicate', true)->with(['original', 'currency', 'account'])->get() AS $transaction)
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="20%">Tegenrekening</th>
                                <th width="20%">Rekening</th>
                                <th width="8%" class="text-right">Bedrag</th>
                                <th width="8%" class="text-center">Boekdatum</th>
                                <th>Omschrijving en/of Referentie</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="transactions">
                            <tr>
                                <td class="monospaced nowrap">
                                    {{ $transaction->original->contra_account_name }}

                                    @if($transaction->original->contra_account !== null)
                                        <br />{{ $transaction->original->contra_account }}
                                    @endif
                                </td>
                                <td class="monospaced nowrap">
                                    {{ $transaction->original->account->name }}<br />{{ $transaction->original->account->account }}
                                </td>
                                <td class="text-right monospaced nowrap currency {{ $transaction->dw }}">{{ \CurrencyHelper::readable($transaction->original->currency->code, $transaction->original->amount, (($transaction->original->dw === 'withdrawal') ? true : false)) }}</td>
                                <td class="text-center nowrap monospaced">{{ $transaction->original->book_date->format('d-m-Y') }}</td>
                                <td class="monospaced">{{ $transaction->original->description }} {{ $transaction->original->reference }}</td>
                                <td class="text-center" rowspan="2">
                                    <a href="{{ route('transaction-do-duplicate-save', ['id' => $transaction->id]) }}" class="btn btn-xs btn-success">Bewaar</a><br /><br />
                                    <a href="{{ route('transaction-do-duplicate-delete', ['id' => $transaction->id]) }}" class="btn btn-xs btn-danger">Verwijder</a>
                                </td>
                            </tr>

                            <tr>
                                <td class="monospaced nowrap">
                                    {{ $transaction->contra_account_name }}

                                    @if($transaction->contra_account !== null)
                                        <br />{{ $transaction->contra_account }}
                                    @endif
                                </td>
                                <td class="monospaced nowrap">
                                    {{ $transaction->account->name }}<br />{{ $transaction->account->account }}
                                </td>
                                <td class="text-right monospaced nowrap currency {{ $transaction->dw }}">{{ \CurrencyHelper::readable($transaction->currency->code, $transaction->amount, (($transaction->dw === 'withdrawal') ? true : false)) }}</td>
                                <td class="text-center nowrap monospaced">{{ $transaction->book_date->format('d-m-Y') }}</td>
                                <td class="monospaced">{{ $transaction->description }} {{ $transaction->reference }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <br /><br />
                @endforeach
            </div>
        </div>
    @else
        <div class="alert alert-info">Geen dubbele transacties gevonden.</div>
    @endif
@endsection