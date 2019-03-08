<div class="form-group {{ ($errors->has($id) ? 'has-error' : ($errors->any() ? 'has-success' : '')) }}">
    <label>{{ $name }}</label>
    <select name="{{ $id }}" class="form-control select2">
        @foreach($options AS $option)
            @if(old($id, isset($value) ? $value : null) === $option['key'])
                <option value="{{ $option['key'] }}" selected="selected">{{ $option['value'] }}</option>
            @else
                <option value="{{ $option['key'] }}">{{ $option['value'] }}</option>
            @endif
        @endforeach
    </select>

    @if($errors->has($id))
        <div class="help-block">{{ $errors->default->first($id) }}</div>
    @endif
</div>