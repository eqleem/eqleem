@props([
    'name' => '',
    'class' => '',
    'iconclass' => '',
    'path' => 'assets/icons',
    'dir' => 'tabler',
    'type' => 'tabler',
    'size' => '5',
])
@if ($type == 'tabler')
    <span {{ $attributes }}>
        {!! str_replace(
            '<svg',
            '<svg class="inline-block dark:text-gray-500 ' . $class . ' ' . $iconclass . ' size-' . $size . ' "',
            file_get_contents(public_path("$path/$dir/$name.svg")),
        ) !!}
    </span>
@else
    <span {{ $attributes }} class="text-2xl"> {{ $name }} </span>
@endif
