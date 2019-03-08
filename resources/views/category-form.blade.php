@extends('template')

@section('title', 'Categoriën')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            @if(isset($category))
                <form method="POST" action="{{ route('category-do-edit', ['id' => $category->id]) }}">
            @else
                <form method="POST" action="{{ route('category-do-create') }}">
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

                        @if(isset($category))
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam', 'value' => $category->name])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving', 'value' => $category->description])
                            @include('helpers.select_old', ['id' => 'currency_id', 'name' => 'Valuta', 'options' => $currencies, 'value' => $category->currency_id])

                            @include('helpers.select', ['id' => 'icon', 'name' => 'Icoon', 'options' => $icons, 'old' => $category->icon])
                        @else
                            @include('helpers.input', ['id' => 'name', 'name' => 'Naam'])
                            @include('helpers.input', ['id' => 'description', 'name' => 'Omschrijving'])
                            @include('helpers.select_old', ['id' => 'currency_id', 'name' => 'Valuta', 'options' => $currencies])

                            @include('helpers.select', ['id' => 'icon', 'name' => 'Icoon', 'options' => $icons])
                        @endif
                    </div>
                    <div class="box-footer">
                        @if(isset($category))
                            <button name="submit" type="submit" value="edit" class="btn btn-success">Wijzigen</button>
                        @else
                            <button name="submit" type="submit" value="create" class="btn btn-success">Aanmaken</button>
                        @endif

                        <a href="{{ route('categories') }}" class="btn btn-info">Annuleren</a>
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

    @if(isset($category))
        <div class="row">
            <div class="col-md-6">
                <form method="POST" action="{{ route('category-do-edit-conditions', ['id' => $category->id]) }}">
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

                            @if($category->conditions !== null)
                                <tbody>
                                    @foreach(json_decode($category->conditions) AS $condition)
                                        <tr>
                                            <td>{{ __('rules.' . $condition->type) }}</td>
                                            <td>{{ $condition->data }}</td>
                                            <td class="text-right"><a href="{{ route('category-do-delete-condition', ['id' => $category->id, 'i' => $loop->index]) }}" class="btn btn-xs btn-danger">Verwijder</a></td>
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