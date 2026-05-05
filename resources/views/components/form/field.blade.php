@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'placeholder' => null])
<div class="flex flex-col gap-1">
    @if($label)
        <label for="{{ $name }}" class="text-[12px] font-medium text-ink-2">
            {{ $label }}@if($required)<span class="text-danger ml-0.5">*</span>@endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'input']) }}
    />
    @error($name)
        <p class="text-[11.5px] text-danger">{{ $message }}</p>
    @enderror
</div>
