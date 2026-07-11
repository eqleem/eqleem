@props([
    'value' => null,
    'name' => 'text',
    'label' => null,
    'subtitle' => '',
    'placeholder' => '',
    'options' => [],
    'allowCustom' => false,
])

@php
    $isCustom = is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value);
    $customHex = $isCustom ? $value : '#3d5ccc';
@endphp

<div class="bg-black/5 p-1 rounded-lg">
    <div class=" sm:flex items-center">
        @if ($label)
            <label for="{{ $name }}"
                class=" inline-block text-sm text-gray-500 font-semibold p-2 flex-shrink-0 w-36"> <span
                    class=" ">{{ $label }}</span> </label>
        @endif

        <div
            class="flex flex-wrap items-center gap-0.5"
            x-data="{
                model: @entangle($name),
                customHex: @js($customHex),
                isHex(value) {
                    return typeof value === 'string' && /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value);
                },
                selectCustom(event) {
                    this.customHex = event.target.value;
                    this.model = event.target.value;
                },
            }"
        >
            @if ($options == array_values($options))
                @foreach ($options as $item)
                    <div>
                        <input x-model="model" name="color" type="radio" value="{{ $item }}"
                            id="pick-{{ $name }}-{{ $item }}" class="peer hidden  "
                            @if ($value == $item) checked @endif />

                        <label for="pick-{{ $name }}-{{ $item }}"
                            class="p-0.5 rounded-lg hover:bg-black/10 peer-checked:border-black block border-2 border-transparent cursor-pointer"
                            title="{{ __(ucfirst($item)) }}">
                            <div class="w-7 h-7 rounded-md bg-{{ $item }}-500 bg-black/10"></div>
                        </label>
                    </div>
                @endforeach
            @endif

            @if ($allowCustom)
                <label
                    class="relative block cursor-pointer rounded-lg border-2 p-0.5 transition hover:bg-black/10"
                    :class="isHex(model) ? 'border-black scale-105' : 'border-transparent'"
                    title="لون مخصص"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-300"
                        :style="{ backgroundColor: isHex(model) ? model : customHex }"
                    ></span>
                    <input
                        type="color"
                        class="absolute inset-0 cursor-pointer opacity-0"
                        :value="isHex(model) ? model : customHex"
                        @input="selectCustom($event)"
                    >
                </label>
            @endif

            <span
                x-show="model"
                x-text="model"
                class="ms-1 text-xs font-medium text-stone-600"
            ></span>
        </div>

    </div>
</div>
