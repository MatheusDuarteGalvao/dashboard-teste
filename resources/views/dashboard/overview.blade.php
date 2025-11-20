@extends('layouts.app')

@section('title', 'Dashboard - Overview')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Overview</h1>

    {{-- Cards --}}
    @php $d = $data ?? [] @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white shadow rounded-lg p-5">
            <h2 class="text-gray-500 text-sm">Total de Pedidos</h2>
            <p class="text-2xl font-bold">{{ number_format($d['total_orders'] ?? 0, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <h2 class="text-gray-500 text-sm">Receita Total</h2>
            <p class="text-2xl font-bold text-green-600">
                R$ {{ number_format($d['total_revenue_brl'] ?? 0, 2, ',', '.') }}
                @if(!is_null($d['total_revenue_usd']))
                    <span class="text-sm text-gray-500 block mt-1">USD {{ number_format($d['total_revenue_usd'], 2, '.', ',') }}</span>
                @endif
            </p>
            @if(!empty($d['avg_orders_per_customer']))
                <div class="text-xs text-gray-500 mt-2">Média pedidos/cliente: {{ $d['avg_orders_per_customer'] }}</div>
            @endif
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <h2 class="text-gray-500 text-sm">Pedidos Entregues</h2>
            <p class="text-2xl font-bold">{{ number_format($d['delivered_count'] ?? 0, 0, ',', '.') }}</p>
            <div class="text-sm text-gray-500 mt-2">{{ $d['delivery_rate'] ?? 0 }}% entregues</div>
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <h2 class="text-gray-500 text-sm">Clientes Únicos</h2>
            <p class="text-2xl font-bold">{{ number_format($d['unique_customers'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Resumo financeiro e produto mais vendido --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white shadow rounded-lg p-5">
            <h3 class="text-lg font-medium">Resumo Financeiro</h3>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-3 rounded">
                    <div class="text-xs text-gray-500">Bruto</div>
                    <div class="text-lg font-semibold">R$ {{ number_format($d['gross'] ?? 0, 2, ',', '.') }}</div>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <div class="text-xs text-gray-500">Reembolsos</div>
                    <div class="text-lg font-semibold text-red-600">R$ {{ number_format($d['refunds'] ?? 0, 2, ',', '.') }}</div>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <div class="text-xs text-gray-500">Líquido</div>
                    <div class="text-lg font-semibold">R$ {{ number_format($d['net'] ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-lg font-medium">Taxa de Reembolso</h3>
            <div class="mt-4 text-3xl font-semibold text-red-600">{{ $d['refund_rate'] ?? 0 }}%</div>
            <div class="mt-3 text-sm text-gray-500">Pedidos reembolsados / total</div>
        </div>
    </div>

    {{-- Produto mais vendido --}}
    <div class="bg-white shadow rounded-lg p-5 mb-8">
        <h3 class="text-lg font-medium">Produto Mais Vendido</h3>
        @if(!empty($d['top_product']))
            <div class="mt-3">
                <div class="font-medium">{{ data_get($d, 'top_product.name') ?? data_get($d, 'top_product.title') }}</div>
                <div class="text-sm text-gray-500">Qtd: {{ data_get($d, 'top_product.qty') ?? data_get($d, 'top_product.quantity') ?? '—' }}</div>
                <div class="text-sm text-gray-500">Receita: R$ {{ number_format(data_get($d, 'top_product.revenue') ?? 0, 2, ',', '.') }}</div>
            </div>
        @else
            <div class="text-sm text-gray-500 mt-2">Sem dados</div>
        @endif
    </div>

    {{-- Tabela de Pedidos - mesmo template do index --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Pedidos Recentes</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm">
                    <th class="p-3">ID</th>
                    <th class="p-3">Cliente</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Entrega</th>
                    <th class="p-3">Valor</th>
                    <th class="p-3">Data</th>
                </tr>
            </thead>
            <tbody>
                @php $ordersList = $orders ?? [] @endphp
                @foreach ($ordersList as $order)
                <tr class="border-b text-sm">
                    <td class="p-3 font-mono">{{ data_get($order, 'id') }}</td>
                    <td class="p-3">{{ data_get($order, 'customer.first_name') }} {{ data_get($order, 'customer.last_name') }}</td>
                    <td class="p-3">{{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</td>
                    <td class="p-3">{{ data_get($order, 'financial_status') }}</td>
                    <td class="p-3">{{ data_get($order, 'fulfillment_status') }}</td>
                    <td class="p-3">R$ {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}</td>
                    <td class="p-3">{{ data_get($order, 'created_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'created_at'))) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
