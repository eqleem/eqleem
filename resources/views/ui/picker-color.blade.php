@props([
    'value' => null,
    'name' => 'text',
    'label' => null,
    'subtitle' => '',
    'placeholder' => '',
    'options' => [],
])

<div class="bg-black/5 p-1 rounded-lg">
    <div class=" sm:flex items-center">
        @if ($label)
            <label for="{{ $name }}"
                class=" inline-block text-sm text-gray-500 font-semibold p-2 flex-shrink-0 w-36"> <span
                    class=" ">{{ $label }}</span> </label>
        @endif

        <div class="flex flex-wrap gap-0.5" x-data="{ model: @entangle($name) }">
            @if ($options == array_values($options))
                @foreach ($options as $item)
                    <div>
                        <input x-model="model" name="color" type="radio" value="{{ $item }}"
                            id="pick-{{ $item }}" class="peer hidden  "
                            @if ($value == $item) checked @endif />

                        <label for="pick-{{ $item }}"
                            class="p-0.5 rounded-lg hover:bg-black/10 peer-checked:border-black block border-2 border-transparent cursor-pointer"
                            title="{{ __(ucfirst($item)) }}">
                            <div class="w-7 h-7 rounded-md bg-{{ $item }}-500 bg-black/10"></div>
                        </label>
                    </div>
                @endforeach
            @endif
        </div>

    </div>
</div>
