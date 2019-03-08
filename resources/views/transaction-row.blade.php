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
    <td class="nowrap">
        @if($transaction->budget !== null)
            <a href="{{ route('budget', ['id' => $transaction->budget->id]) }}"><span class="label label-success">{{ $transaction->budget->name }}</span></a>
        @endif

        @if($transaction->category !== null)
            <a href="{{ route('category', ['id'  => $transaction->category->id]) }}"><span class="label label-info">{{ $transaction->category->name }}</span></a>
        @endif

        <!-- 
        <a href="#"><span class="label label-warning">Tag1</span></a>
        <a href="#"><span class="label label-warning">Tag2</span></a> -->
    </td>
    <td class="text-right nowrap">
        <a href="{{ route('transaction-do-delete', ['id' => $transaction->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('U staat op het punt om een transactie te verwijderen. Weet u dit zeker?')">Verwijder</a>
    </td>
</tr>