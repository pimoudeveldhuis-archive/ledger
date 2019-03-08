@extends('template')

@section('title')
    {{ $budget->name }}
@endsection

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Overzicht</h3>
                </div>

                <div class="box-body no-padding">
                    <canvas id="chartOverview" cache="{{ Auth::user()->cache('budget-'. $budget->id .'-overview', false) }}"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Ten opzichte van vorig jaar</h3>
                        </div>

                        <div class="box-body no-padding">
                            <canvas id="chartComparingPreviousYear" cache="{{ Auth::user()->cache('budget-'. $budget->id .'-comparingPreviousYear', false) }}"></canvas>
                        </div>
                    </div>
                </div>
                    <div class="col-md-6">
                        <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Lange termijn patroon</h3>
                        </div>

                        <div class="box-body no-padding">
                            <canvas id="chartLongterm" cache="{{ Auth::user()->cache('budget-'. $budget->id .'-longterm', false) }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->transactions()->where('user_budget_id', $budget->id)->orderBy('book_date', 'DESC')->whereYear('book_date', 2019)->whereMonth('book_date', 1)->count() > 0)
                <div class="box">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Transacties</h3>
                        </div>

                        <div class="box-body no-padding">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tegenrekening</th>
                                        <th>Rekening</th>
                                        <th class="text-right">Bedrag</th>
                                        <th class="text-center">Boekdatum</th>
                                        <th>Omschrijving en/of Referentie</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(Auth::user()->transactions()->where('user_budget_id', $budget->id)->orderBy('book_date', 'DESC')->whereYear('book_date', 2019)->whereMonth('book_date', 1)->with(['budget', 'category', 'currency', 'account'])->get() AS $transaction)
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Uitleg</h3>
                </div>

                <div class="box-body">
                    <p>TODO</p>
                </div>
            </div>

            <form method="POST" action="{{ route('budget-do-edit-amounts', ['id' => $budget->id]) }}">
                @csrf

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Budget aanpassen</h3>
                    </div>

                    <div class="box-body">
                        @include('helpers.select', ['id' => 'year', 'name' => 'Jaar', 'options' => \DateHelper::getYearsArray(Auth::user()->oldest_transaction->format('Y')), 'old' => \DateHelper::currentYear()])

                        @foreach(\DateHelper::getMonthsArray() AS $month)
                            @include('helpers.input', ['id' => 'month[' . $month .']', 'name' => __('date.months.' . \DateHelper::getMonthName($month))])
                        @endforeach
                    </div>

                    <div class="box-footer">
                        <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script type="text/javascript">
        var budget_id = "{{ $budget->id }}";
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
    <script src="/js/budget.js"></script>
@endsection