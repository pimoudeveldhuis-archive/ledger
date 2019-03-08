<div class="form-group {{ ($errors->has($id) ? 'has-error' : ($errors->any() ? 'has-success' : '')) }}">
    <label>{{ $name }}</label>
    <select id="input_{{ $id }}" name="{{ $id }}" class="form-control select2">
        @foreach($options AS $key => $value)
            @if(old($id, isset($old) ? $old : null) == $key)
                <option value="{{ $key }}" selected="selected">{{ $value }}</option>
            @else
                <option value="{{ $key }}">{{ $value }}</option>
            @endif
        @endforeach
    </select>

    @if($errors->has($id))
        <div class="help-block">{{ $errors->default->first($id) }}</div>
    @endif
</div>