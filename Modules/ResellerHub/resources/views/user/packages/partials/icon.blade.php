@php use Modules\ResellerHub\App\Enums\PackageIcon; @endphp
@props([
    'icon' => 'cube',
    'class' => 'w-5 h-5'
])

@php
    $class = $class ?? 'w-5 h-5';
    $packageIcon = PackageIcon::fromOrDefault($icon ?? 'cube');
@endphp

{!! $packageIcon->svg($class) !!}
