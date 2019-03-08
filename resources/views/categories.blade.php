@extends('template')

@section('title', 'Categoriën')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            @if(Auth::user()->categories()->count() > 0)
                <div class="box">
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Omschrijving</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(Auth::user()->categories()->orderBy('name', 'ASC')->limit(400)->get() AS $category)
                                    <tr>
                                        <td>
                                            @if($category->icon !== null)
                                                <i class="fa fa-{{ $category->icon }}"></i>&nbsp;{{ $category->name }}
                                            @else
                                                {{ $category->name }}
                                            @endif
                                        </td>
                                        <td>{{ $category->description }}</td>
                                        <td class="text-right">
                                            @if($category->transactions()->count() > 0)
                                                <a href="{{ route('category', ['id' => $category->id]) }}" class="btn btn-xs btn-success">Bekijken</a>
                                            @endif
                                            
                                            <a href="{{ route('category-do-run', ['id' => $category->id]) }}" class="btn btn-xs btn-info">Regels toepassen</a>
                                            <a href="{{ route('category-edit', ['id' => $category->id]) }}" class="btn btn-xs btn-warning">Wijzigen</a>
                                            <a href="{{ route('category-do-delete', ['id' => $category->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('U staat op het punt om de categorie {{ $category->name }} te verwijderen. Weet u dit zeker?')">Verwijder</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">Geen categoriën gevonden. <a href="{{ route('category-create') }}">Klik hier</a> om je eerste categorie te maken.</div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Uitleg</h3>
                </div>

                <div class="box-body">
                    <p>TODO</p>
                    <p class="text-center"><a class="btn btn-success" href="{{ route('category-create') }}">Nieuwe categorie</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection