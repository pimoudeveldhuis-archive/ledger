@extends('template')

@section('title', 'Probleem met importeren')

@section('content')
    @if(session('_alert'))
        <div class="alert alert-{{ session('_alert')['type'] }}">{{ session('_alert')['msg'] }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <form method="POST" action="{{ route('import-do-error', ['uuid' => $import->uuid]) }}">
                    @csrf

                    <div class="box-body">
                        <p>Het bestand kon niet worden geïmporteerd. De volgende fouten zijn gevonden in uw csv bestand:</p>
                        <ul>
                            @foreach(json_decode($import->errors) AS $error)
                                <li>
                                    @if($error->err === 'dw_empty')
                                        Er kon niet worden vastgesteld of het een bijschrijving of afschrijving betreft                                 
                                    @elseif($error->err === 'currency_id_empty')
                                        De valute van de transactie kon niet worden vastgesteld
                                    @elseif($error->err === 'user_account_id_empty')
                                        Het IBAN nr. komt niet overeen met een van uw rekening nummers
                                    @elseif($error->err === 'book_date_empty')
                                        De boekdatum van de transactie kon niet worden vastgesteld
                                    @elseif($error->err === 'type_empty')
                                        Het type transactie kon niet worden vastgesteld
                                    @elseif($error->err === 'contra_account_empty')
                                        De tegenrekening kon niet worden vastgesteld
                                    @else
                                        {{ $error->err }}
                                    @endif

                                    (regel {{ $error->line_nr }})
                                </li>
                            @endforeach
                        </ul>

                        <p>Normaal kunnen wij niet bij uw bank gegevens of transacties. Om dit voor u op te lossen hebben wij uw toestemming nodig om eenmalig dit bestand in te zien. Nadat wij het probleem hebben vastgesteld wordt deze toegang direct weer ingetrokken en kunnen wij niet meer bij de informatie.</p>
                        @include('helpers.checkbox', ['id' => 'permission', 'label' => 'Ik geef de toestemming zoals hierboven wordt gevraagd.'])
                    </div>

                    <div class="box-footer">
                        <button type="submit" name="submit" class="btn btn-info">Hulpverzoek insturen</button>
                    </div>
                </form>
            </div>
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

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Bestand</h3>
        </div>
        <div class="box-body">
            <p>Hieronder ziet u het bestand zoals deze verstuurd is. Wanneer u ons toestemming geeft om het probleem vast te stellen is onderstaande waar wij eenmalig toegang tot hebben:</p>
            <textarea style="width:100%; height: 400px;">{{ $import->file }}</textarea>
        </div>
    </div>
@endsection