@extends('template')

@section('title', 'Budgetten')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            @if(isset($budget))
                <form method="POST" action="{{ route('budget-do-edit', ['id' => $budget->id]) }}">
            @else
                <form method="POST" action="{{ route('budget-do-create') }}">
            @endif

                @csrf

                <div class="box">
                    <div class="box-body">
                        @php
                            $icons = [];
                            $icons[''] = 'Geen';

                            $options = \Conf::get('fa-icons');
                            if($options !== null) {
                                foreach($options AS $option) {
                                    $icons[$option] = __('fa-icons.' . $option);
                                }
                            }
                        @endphp
                        
                        @if(isset($budget))
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam', 'value' => $budget->name])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving', 'value' => $budget->description])

                            @include('helpers.input', ['id' => 'default_amount', 'name' => 'Standaard budget', 'value' => \CurrencyHelper::readable($budget->currency->code, $budget->default_amount)])
                            @include('helpers.select_old', ['id' => 'currency_id', 'name' => 'Valuta', 'options' => $currencies, 'value' => $budget->currency_id])

                            @include('helpers.select', ['id' => 'icon', 'name' => 'Icoon', 'options' => $icons, 'old' => $budget->icon])
                        @else
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam'])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving'])

                            @include('helpers.input', ['id' => 'default_amount', 'name' => 'Standaard budget'])
                            @include('helpers.select_old', ['id' => 'currency_id', 'name' => 'Valuta', 'options' => $currencies])

                            @include('helpers.select', ['id' => 'icon', 'name' => 'Icoon', 'options' => $icons])
                        @endif
                    </div>
                    <div class="box-footer">
                        @if(isset($budget))
                            <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                        @else
                            <button name="submit" type="submit" value="create" class="btn btn-success">Aanmaken</button>
                        @endif

                        <a href="{{ route('budgets') }}" class="btn btn-info">Annuleren</a>
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

    @if(isset($budget))
        <div class="row">
            <div class="col-md-6">
                <form method="POST" action="{{ route('budget-do-edit-conditions', ['id' => $budget->id]) }}">
                    @csrf

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Regel toevoegen</h3>
                        </div>

                        <div class="box-body">
                            @php
                                $options = \Conf::get('rules');
                                if($options !== null) {
                                    foreach($options AS &$option) {
                                        $option = ['key' => $option, 'value' => __('rules.' . $option)];
                                    }
                                }
                            @endphp

                            @include('helpers.select_old', ['id' => 'type', 'name' => 'Conditie', 'options' => $options])
                            
                            @include('helpers.input', ['id' => 'data', 'name' => 'Waarde'])
                        </div>

                        <div class="box-footer">
                            <button name="submit" type="submit" value="condition" class="btn btn-success">Toevoegen</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Regels</h3>
                    </div>

                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="40%">Conditie</th>
                                    <th>Waarde</th>
                                    <th></th>
                                </tr>
                            </thead>

                            @if($budget->conditions !== null)
                                <tbody>
                                    @foreach(json_decode($budget->conditions) AS $condition)
                                        <tr>
                                            <td>{{ __('rules.' . $condition->type) }}</td>
                                            <td>{{ $condition->data }}</td>
                                            <td class="text-right"><a href="{{ route('budget-do-delete-condition', ['id' => $budget->id, 'i' => $loop->index]) }}" class="btn btn-xs btn-danger">Verwijder</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection