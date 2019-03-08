<div class="form-group {{ ($errors->has($id) ? 'has-error' : ($errors->any() ? 'has-success' : '')) }}">
    <label>{{ $name }}</label>
    <input name="{{ $id }}" type="password" class="form-control" placeholder="{{ $name }}" />

    @if($errors->has($id))
        <div class="help-block">{{ $errors->default->first($id) }}</div>
    @endif
</div>