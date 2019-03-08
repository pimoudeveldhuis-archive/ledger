@extends('template')

@section('title')
    {{ $category->name }}
@endsection

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="box">
        <div class="box-body no-padding">
            <canvas id="barChart" data="{{ Auth::user()->cache('category-'. $category->id .'-overview', false) }}" style="height: 230px; width: 675px;" width="1350" height="460"></canvas>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
    <script src="/js/category.js"></script>
@endsection