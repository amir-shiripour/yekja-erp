@extends('layouts.user')

@section('title', 'اطلاعات اشتراک و نماینده فروش')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('resellerhub::tenant.partials.subscription-content')
</div>
@endsection
