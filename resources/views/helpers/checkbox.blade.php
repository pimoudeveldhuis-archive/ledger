<div class="form-group {{ ($errors->has($id) ? 'has-error' : ($errors->any() ? 'has-success' : '')) }}">
    <div class="checkbox">
        <label>
            <input name="{{ $id }}" name="checkbox" type="checkbox" value="1" {{ old($id, 0) ? 'checked="checked"' : '' }}> {{ $label }}
        </label>
    </div>
</div>