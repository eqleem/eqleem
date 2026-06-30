@props([
    'class' => null,
    'value' => null,
    'name' => null,
    'type' => 'text',
    'inputWidth' => 'w-full',
    'disabled' => (bool)false,
    'label' => null,
    'focus' => false,
    'errormsg' => true,
    'dir' => null,
    'key' => null,
    'icon' => null,
    'description' => '',
    'info' => '',
    'width' => 'w-full',
    'placeholder' => '',
    'prefix' => null,
    'suffix' => null,
    'labelWidth' => 'w-36',
    'block' => false,
    'prime' => false,
    'infoDir' => null,
])

@php $iconTrailing = $attributes->get('icon:trailing'); @endphp

<ui:field name="{{ $name }}" :info="$info" :label="__($label)" :prefix="$prefix" :suffix="$suffix" :dir="$dir" :infoDir="$infoDir" :width="$width" :labelWidth="$labelWidth" :errormsg="$errormsg" :key="$key" :block="$block" :prime="$prime">
 
        {{ $slot }}
   
</ui:field>
