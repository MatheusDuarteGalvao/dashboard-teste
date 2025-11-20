@extends('layouts.app')

@section('title', 'Top Produtos')

@section('content')
<div class="space-y-4">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Top Produtos</h1>
        <div class="text-sm text-gray-500">Top 5 por padrão</div>
    </header>

    <div class="bg-white rounded-lg shadow-sm p-4">
        @php $list = $products ?? [] @endphp

        <ul class="space-y-3">
            @forelse($list as $p)
                <li class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $p['name'] ?? $p->name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">Qtd: {{ $p['qty'] ?? $p->quantity ?? 0 }}</div>
                    </div>
                    <div class="text-sm font-semibold">R$ {{ number_format($p['revenue'] ?? $p->revenue ?? 0, 2, ',', '.') }}</div>
                </li>
            @empty
                <li class="text-sm text-gray-500">Sem dados</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
