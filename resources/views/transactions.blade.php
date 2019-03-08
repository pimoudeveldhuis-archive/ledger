@extends('template')

@section('title', 'Transactie overzicht')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    @if(Auth::user()->transactions()->count() > 0)
        <div class="box">
            <div class="box-body">
                <form id="ajax_filter">
                    <div class="row">
                        <div class="col-md-3">
                            @include('helpers.select', ['id' => 'account', 'name' => 'Rekening', 'options' => $filters['accounts']])
                        </div>
                        <div class="col-md-2">
                            @include('helpers.select', ['id' => 'budget', 'name' => 'Budget', 'options' => $filters['budgets']])
                        </div>
                        <div class="col-md-2">
                            @include('helpers.select', ['id' => 'category', 'name' => 'Categorie', 'options' => $filters['categories']])
                        </div>
                        <div class="col-md-2 col-md-offset-1">
                            @include('helpers.select', ['id' => 'month', 'name' => 'Maand', 'options' => $filters['months']])
                        </div>
                        <div class="col-md-2">
                            @include('helpers.select', ['id' => 'year', 'name' => 'Jaar', 'options' => $filters['years']])
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="box">
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tegenrekening</th>
                            <th>Rekening</th>
                            <th class="text-right">Bedrag</th>
                            <th class="text-center">Boekdatum</th>
                            <th>Omschrijving en/of Referentie</th>
                            <th class="text-center"></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="transactions">
                        @foreach(Auth::user()->transactions()->whereYear('book_date', \Carbon\Carbon::now()->year)->whereMonth('book_date', \Carbon\Carbon::now()->month)->orderBy('book_date', 'DESC')->with(['budget', 'category', 'currency', 'account'])->get() AS $transaction)
                            @include('transaction-row', ['transaction' => $transaction])
                        @endforeach
                    </tbody>
                </table>

                <div class="loader">
                    <i class="fa fa-spinner fa-spin" style="font-size:50px"></i>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">Geen transacties gevonden. <a href="{{ route('import-create') }}">Klik hier</a> om je eerste transacties te importeren.</div>
    @endif
@endsection

@section('js')
    <script type="text/javascript">
        var ajax_call = null;
        var block_loader = false;
        var filters = {};
        var loaded_till = '{{ \DateHelper::currentYear() }}-{{ \DateHelper::currentMonth() }}';
    </script>

<script src="/js/transactions_filter.js"></script>
<script src="/js/transactions_scroll.js"></script>
@endsection