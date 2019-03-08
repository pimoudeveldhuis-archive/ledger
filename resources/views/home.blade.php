@extends('template')

@section('title', 'Home')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    @if(Auth::user()->oldest_transaction === null)
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Uitleg</h3>
                <div class="box-tools pull-right">
                    <div class="btn-group">
                        <a href="#" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <p>Welkom {{ Auth::user()->name }},</p>
                <p>Om te beginnen kunt u onderstaand stappenplan gebruiken. De items op de onderstaande lijst verdwijnen automatisch zodra u ze heeft afgerond. Deze uitleg verdwijnt ook automatisch zodra u uw eerste transacties importeert.</p>
                <ul>
                    @if(Auth::user()->budgets()->count() === 0)
                        <li><a href="{{ route('budget-create') }}">Maak een budget aan</a></li>
                    @endif

                    @if(Auth::user()->budgets()->count() === 0 || Auth::user()->budgets()->value('conditions') === null)
                        <li><a href="{{ route('budget-edit', ['id' => Auth::user()->budgets()->value('id')]) }}">Stel de regels van uw eerste budget in</a></li>
                    @endif

                    @if(Auth::user()->categories()->count() === 0)
                        <li><a href="{{ route('category-create') }}">Maak een categorie aan</a></li>
                    @endif

                    @if(Auth::user()->categories()->count() === 0 || Auth::user()->categories()->value('conditions') === null)
                        <li><a href="{{ route('category-edit', ['id' => Auth::user()->categories()->value('id')]) }}">Maak de regels van uw eerste categorie aan</a></li>
                    @endif

                    <li><a href="{{ route('import-create') }}">Importeer uw eerste transacties</a></li>
                </ul>
            </div>
        </div>
    @else
        @if($import_alert === true)
            <div class="alert alert-warning">Wij hebben nog geen transacties van u over de afgelopen maand. U kunt deze nu <a href="{{ route('import-create') }}">hier</a> importeren.</div>
        @endif
        
        <div class="row">
            <div class="col-md-6">
                @if(Auth::user()->accounts()->count() > 0)
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rekeningen</h3>
                        </div>

                        <div class="box-body no-padding">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th width="35%" class="text-right">{{ \DateHelper::displayPreviousMonth() }}</th>
                                        <th width="35%" class="text-right">Laatste 12 mnd</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(Auth::user()->accounts()->orderBy('name', 'ASC')->limit(400)->get() AS $account)
                                        @if(Auth::user()->cache('account-'. $account->id .'-home') !== null)
                                            <tr>
                                                <td>{{ $account->name }}<br /><i>{{ $account->description }}</i><br /><span class="monospaced">{{ $account->account }}</td>
                                                <td class="text-right monospaced currency">
                                                    <span class="green">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previousMonth->deposit) }}</span><br />
                                                    <span class="red">- {{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previousMonth->withdrawal) }}</span><br />
                                                    <span class="{{ (Auth::user()->cache('account-'. $account->id .'-home')->previousMonth->result >= 0) ? 'green' : 'red' }}">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previousMonth->result, (Auth::user()->cache('account-'. $account->id .'-home')->previousMonth->result < 0)) }}</span>
                                                </td>
                                                <td class="text-right monospaced currency">
                                                    <span class="green">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previous12months->deposit) }}</span><br />
                                                    <span class="red">- {{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previous12months->withdrawal) }}</span><br />
                                                    <span class="{{ (Auth::user()->cache('account-'. $account->id .'-home')->previous12months->result >= 0) ? 'green' : 'red' }}">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('account-'. $account->id .'-home')->previous12months->result) }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">Geen rekeningen gevonden. <a href="{{ route('account-create') }}">Klik hier</a> om je eerste rekening te maken.</div>
                @endif
                
                @if(Auth::user()->budgets()->count() > 0)
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Budgetten</h3>
                        </div>

                        <div class="box-body no-padding">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th width="25%" class="text-right">{{ \DateHelper::displayPreviousMonth() }}</th>
                                        <th width="25%" class="text-right">Laatste 6 mnd</th>
                                        <th width="25%" class="text-right">Laatste 12 mnd</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(Auth::user()->budgets()->orderBy('name', 'ASC')->limit(400)->get() AS $budget)
                                        @if(Auth::user()->cache('budget-'. $budget->id .'-home') !== null)
                                            <tr>
                                                <td>
                                                    @if($budget->icon !== null)
                                                        <i class="fa fa-{{ $budget->icon }}"></i>&nbsp;{{ $budget->name }}
                                                    @else
                                                        {{ $budget->name }}
                                                    @endif
                                                </td>

                                                @if(Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonth > Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonthBudget)
                                                    <td class="text-right monospaced currency red">{{ \CurrencyHelper::readable($budget->currency->code, Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonth) }} (+ {{ \CurrencyHelper::readable($budget->currency->code, (Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonth - Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonthBudget)) }})</td>
                                                @else
                                                    <td class="text-right monospaced currency green">{{ \CurrencyHelper::readable($budget->currency->code, Auth::user()->cache('budget-'. $budget->id .'-home')->previousMonth) }}</td>
                                                @endif

                                                <td class="text-right monospaced currency">{{ \CurrencyHelper::readable($budget->currency->code, Auth::user()->cache('budget-'. $budget->id .'-home')->previous6months) }}</td>
                                                <td class="text-right monospaced currency">{{ \CurrencyHelper::readable($budget->currency->code, Auth::user()->cache('budget-'. $budget->id .'-home')->previous12months) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->categories()->count() > 0)
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Categorieën</h3>
                        </div>

                        <div class="box-body no-padding">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th width="25%" class="text-right">{{ \DateHelper::displayPreviousMonth() }}</th>
                                        <th width="25%" class="text-right">Laatste 6 mnd</th>
                                        <th width="25%" class="text-right">Laatste 12 mnd</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(Auth::user()->categories()->orderBy('name', 'ASC')->limit(400)->get() AS $category)
                                        @if(Auth::user()->cache('category-'. $category->id .'-home') !== null)
                                            <tr>
                                                <td>
                                                    @if($category->icon !== null)
                                                        <i class="fa fa-{{ $category->icon }}"></i>&nbsp;{{ $category->name }}
                                                    @else
                                                        {{ $category->name }}
                                                    @endif
                                                </td>

                                                <td class="text-right monospaced currency">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('category-'. $category->id .'-home')->previousMonth) }}</td>
                                                <td class="text-right monospaced currency">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('category-'. $category->id .'-home')->previous6months) }}</td>
                                                <td class="text-right monospaced currency">{{ \CurrencyHelper::readable('EUR', Auth::user()->cache('category-'. $category->id .'-home')->previous12months) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
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

                @if($import_alert === false)
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Budget overzicht {{ \DateHelper::displayPreviousMonth() }}</h3>
                        </div>

                        <div class="box-body no-padding">
                            <canvas id="chartBudgets" cache="{{ Auth::user()->cache('budgets-barchart', false) }}"></canvas>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Categorieën overzicht {{ \DateHelper::displayPreviousMonth() }}</h3>
                        </div>

                        <div class="box-body no-padding">
                            <canvas id="chartCategories" cache="{{ Auth::user()->cache('categories-barchart', false) }}"></canvas>
                        </div>
                </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
    <script src="/js/home.js"></script>
@endsection