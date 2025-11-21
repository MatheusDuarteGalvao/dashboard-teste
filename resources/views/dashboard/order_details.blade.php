@extends('layouts.app')

@section('title', 'Pedidos - Detalhes')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Detalhes do Pedido #{{ data_get($order, 'id') ?? '—' }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-4">Informações do Cliente</h2>
                <p><strong>Nome:</strong> {{ data_get($order, 'customer.first_name') }} {{ data_get($order, 'customer.last_name') }}</p>
                <p><strong>Email:</strong> {{ data_get($order, 'contact_email') ?? data_get($order, 'customer.email') }}</p>
                <p><strong>Cidade:</strong> {{ data_get($order, 'customer.city') ?? '—' }}</p>
                <p><strong>Estado:</strong> {{ data_get($order, 'customer.state') ?? '—' }}</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-4">Detalhes do Pedido</h2>
                <p><strong>Valor Total:</strong> R$ {{ number_format((float) str_replace(',', '', data_get($order, 'local_currency_amount', 0)), 2, ',', '.') }}</p>
                <p><strong>Status do Pagamento:</strong> {{ data_get($order, 'financial_status') ?? '—' }}</p>
                <p><strong>Status da Entrega:</strong> {{ data_get($order, 'fulfillment_status') ?? '—' }}</p>
                <p><strong>Data do Pedido:</strong> {{ data_get($order, 'placed_at') ? date('d/m/Y H:i', strtotime(data_get($order, 'placed_at'))) : '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
