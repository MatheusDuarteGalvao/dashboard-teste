@extends('layouts.app')

@section('title', 'Pedidos — Tabela')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Pedidos</h1>

    {{-- Busca / ações --}}
    <div class="flex items-center justify-between mb-4">
        <input id="q" type="search" placeholder="Buscar pedido, cliente, cidade..." class="px-3 py-2 rounded border w-full sm:w-1/3 focus:outline-none" />
    </div>

    @php $ordersList = $orders['data'] ?? $orders ?? [] @endphp

    {{-- Mobile: lista de cards --}}
    <div class="sm:hidden space-y-3">
        @forelse($ordersList as $order)
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Pedido</div>
                        <div class="font-mono text-sm text-gray-800">{{ data_get($order, 'id') ?? '—' }}</div>

                        <div class="mt-2 text-sm text-gray-700">
                            {{ data_get($order, 'customer.first_name') }} {{ data_get($order, 'customer.last_name') }}
                        </div>
                        <div class="text-xs text-gray-500">{{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-500">Valor</div>
                        <div class="text-lg font-semibold text-gray-800">
                            R$ {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}
                        </div>
                        <div class="mt-2 text-xs text-gray-500">{{ data_get($order, 'created_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'created_at'))) : '—' }}</div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xs text-gray-600">
                        <span class="mr-2">Pagamento: {{ data_get($order, 'financial_status') ?? '—' }}</span>
                        <span>Entrega: {{ data_get($order, 'fulfillment_status') ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">Nenhum pedido encontrado.</div>
        @endforelse
    </div>

    {{-- Desktop / tablet: tabela --}}
    <div class="hidden sm:block bg-white shadow rounded-lg overflow-x-auto">
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
                    <td class="p-3">{{ data_get($order, 'customer.first_name') }} {{ data_get($order, 'customer.last_name') }}</td>
                    <td class="p-3">{{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</td>
                    <td class="p-3">{{ data_get($order, 'financial_status') }}</td>
                    <td class="p-3">{{ data_get($order, 'fulfillment_status') }}</td>
                    <td class="p-3">R$ {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}</td>
                    <td class="p-3">{{ data_get($order, 'created_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'created_at'))) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td class="p-3 text-sm text-gray-500" colspan="8">Nenhum pedido encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
