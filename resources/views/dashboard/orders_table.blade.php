@extends('layouts.app')

@section('title', 'Pedidos — Tabela')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Pedidos</h1>

    {{-- Tabela de Pedidos - mesma estrutura do index --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Todos os Pedidos</h2>
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
                @php $ordersList = $orders['data'] ?? $orders ?? [] @endphp
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
