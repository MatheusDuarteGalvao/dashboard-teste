@extends('layouts.app')

@section('title', 'Dashboard - Overview')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Overview</h1>

        {{-- Cards (mantêm) --}}
        @php $d = $data ?? [] @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            {{-- ...cards existing markup... --}}
            @include('dashboard.components._card', [
                'title' => 'Total de pedidos',
                'value' => number_format($d['total_orders'] ?? 0, 0, ',', '.'),
                'meta' => 'Pedidos registrados',
            ])
            @include('dashboard.components._card', [
                'title' => 'Receita Total',
                'value' => 'R$ ' . number_format($d['total_revenue_brl'] ?? 0, 2, ',', '.'),
                'meta' => 'Receita em BRL',
                'extra' => !is_null($d['total_revenue_usd'])
                    ? 'USD ' . number_format($d['total_revenue_usd'], 2, '.', ',')
                    : null,
            ])
            @include('dashboard.components._card', [
                'title' => 'Pedidos Entregues',
                'value' => number_format($d['delivered_count'] ?? 0, 0, ',', '.'),
                'meta' => ($d['delivery_rate'] ?? 0) . '% entregues',
            ])
            @include('dashboard.components._card', [
                'title' => 'Clientes Únicos',
                'value' => number_format($d['unique_customers'] ?? 0, 0, ',', '.'),
                'meta' => 'Clientes distintos que fizeram pedidos',
            ])
        </div>

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
                        <div class="text-lg font-semibold text-red-600">R$
                            {{ number_format($d['refunds'] ?? 0, 2, ',', '.') }}</div>
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

        <div class="bg-white p-5 shadow rounded mt-8 mb-8">
            <h2 class="text-xl font-bold mb-4">Vendas ao longo do tempo</h2>
            <canvas id="salesOvertime"></canvas>
        </div>

        <div class="bg-white shadow rounded-lg p-5 mb-8">
            <h3 class="text-lg font-medium">Produto Mais Vendido</h3>
            @if (!empty($d['top_product']))
                <div class="mt-3">
                    <div class="font-medium">{{ data_get($d, 'top_product.name') ?? data_get($d, 'top_product.title') }}
                    </div>
                    <div class="text-sm text-gray-500">Qtd:
                        {{ data_get($d, 'top_product.qty') ?? (data_get($d, 'top_product.quantity') ?? '—') }}</div>
                    <div class="text-sm text-gray-500">Receita: R$
                        {{ number_format(data_get($d, 'top_product.revenue') ?? 0, 2, ',', '.') }}</div>
                </div>
            @else
                <div class="text-sm text-gray-500 mt-2">Sem dados</div>
            @endif
        </div>

        {{-- Recent orders: mobile cards + desktop table (mesma estrutura do orders_table) --}}
        @php $ordersList = $orders ?? $recentOrders ?? [] @endphp

        <div class="sm:hidden space-y-3 mb-8">
            @forelse($ordersList as $order)
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Pedido</div>
                            <div class="font-mono text-sm text-gray-800">{{ data_get($order, 'id') ?? '—' }}</div>
                            <div class="mt-2 text-sm text-gray-700">{{ data_get($order, 'customer.first_name') }}
                                {{ data_get($order, 'customer.last_name') }}</div>
                            <div class="text-xs text-gray-500">
                                {{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Valor</div>
                            <div class="text-lg font-semibold">R$
                                {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ data_get($order, 'placed_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'placed_at'))) : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">Nenhum pedido recente.</div>
            @endforelse
        </div>

        <div class="hidden sm:block bg-white rounded-lg overflow-x-auto">
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
                <div class="lg:col-span-2 bg-white rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-8">Pedidos Recentes</h3>
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
                            @forelse($ordersList as $order)
                                <tr class="border-b text-sm">
                                    <td class="p-3 font-mono">{{ data_get($order, 'id') }}</td>
                                    <td class="p-3">{{ data_get($order, 'customer.first_name') }}
                                        {{ data_get($order, 'customer.last_name') }}</td>
                                    <td class="p-3">
                                        {{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</td>
                                    <td class="p-3">{{ data_get($order, 'financial_status') }}</td>
                                    <td class="p-3">{{ data_get($order, 'fulfillment_status') }}</td>
                                    <td class="p-3">R$
                                        {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}
                                    </td>
                                    <td class="p-3">
                                        {{ data_get($order, 'placed_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'placed_at'))) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-3 text-sm text-gray-500" colspan="7">Nenhum pedido recente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 shadow rounded mt-8 mb-8">
        <h2 class="text-2xl font-bold mb-4">Top 5 Produtos</h2>
        <canvas id="chartTopProducts"></canvas>
    </div>

    <div class="bg-white p-5 shadow rounded mt-8 mb-8">
        <h1 class="text-3xl font-bold mb-6">Top 10 Cidades</h1>
        <canvas id="chartTopCities"></canvas>
    </div>

    @push('scripts')
        <script>
            const ctxTopCities = document.getElementById('chartTopCities');

            new Chart(ctxTopCities, {
                type: 'bar',
                data: {
                    labels: @json(collect($topCities)->pluck('city')),
                    datasets: [{
                        label: 'Pedidos',
                        data: @json(collect($topCities)->pluck('total')),
                        borderWidth: 1
                    }]
                }
            });

            const ctxTopProducts = document.getElementById('chartTopProducts');

            new Chart(ctxTopProducts, {
                type: 'bar',
                options: {
                    indexAxis: 'y',
                },
                data: {
                    labels: @json(collect($topProducts)->pluck('name')),
                    datasets: [{
                        label: 'Quantidade Vendida',
                        data: @json(collect($topProducts)->pluck('qty')),
                        borderWidth: 1
                    }]
                }
            });

            const ctxSalesOvertime = document.getElementById('salesOvertime');

            new Chart(ctxSalesOvertime, {
                type: 'line',
                data: {
                    labels: @json(collect($salesOvertime)->pluck('date')),
                    datasets: [{
                        label: 'Receita',
                        data: @json(collect($salesOvertime)->pluck('total')),
                        borderWidth: 1
                    }]
                }
            });
        </script>
    @endpush

    {{-- restante da página --}}
    </div>
@endsection
