<div class="form-group {{ ($errors->has($id) ? 'has-error' : ($errors->any() ? 'has-success' : '')) }}">
    <label>{{ $name }}</label>
    <input id="input_{{ $id }}" name="{{ $id }}" type="text" class="form-control" placeholder="{{ $name }}" value="{{ old($id, isset($value) ? $value : '') }}" />

    @if($errors->has($id))
        <div class="help-block">{{ $errors->default->first($id) }}</div>
    @endif
</div>