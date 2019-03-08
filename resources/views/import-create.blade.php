@extends('template')

@section('title', 'Transacties importeren')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('import-do-create') }}" enctype="multipart/form-data">
                @csrf

                <div class="box">
                    <div class="box-body">
                        @include('helpers.select_old', ['id' => 'import_configuration_id', 'name' => 'Bank', 'options' => $import_configurations])
                        @include('helpers.file', ['id' => 'csv', 'name' => 'CSV Bestand'])
                    </div>
                    <div class="box-footer">
                        <button name="submit" type="submit" value="import" class="btn btn-success">Importeren</button>
                    </div>
                </div>
            </form>

            @if(Auth::user()->imports()->count() > 0)
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Wachtrij</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Import kenmerk</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(Auth::user()->imports AS $import)
                                    <tr>
                                        <td>{{ $import->uuid }}</td>
                                        <td>
                                            @if($import->processed === false)
                                                <span class="label label-info">In wachtrij</span>
                                            @else
                                                @if($import->errors !== null)
                                                    <span class="label label-danger">Probleem met importeren</span>
                                                @else
                                                    <span class="label label-success">Afgerond</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($import->processed === true && $import->errors !== null)
                                                <a href="{{ route('import-do-delete', ['uuid' => $import->uuid]) }}" class="btn btn-xs btn-danger">Verwijderen</a>
                                                <a href="{{ route('import-error', ['uuid' => $import->uuid]) }}" class="btn btn-xs btn-danger">Probleem oplossen</a>
                                            @endif
                                        </td>
                                    </tr>
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
        </div>
    </div>
@endsection