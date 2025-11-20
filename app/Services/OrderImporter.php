<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Refund;
use Carbon\Carbon;
use Throwable;

class OrderImporter
{
    protected $endpoint;
    protected $cacheKey = 'crm:orders:order';

    public function __construct()
    {
        $this->endpoint = config('services.crm.test_api');

        if (empty($this->endpoint)) {
            Log::error('OrderImporter: endpoint CRM não configurado', [
                'services_crm' => config('services.crm'),
                'env_CRM_TEST_API' => env('CRM_TEST_API'),
            ]);
        }
    }

    public function fetch(bool $useCache = true)
    {
        if ($useCache && Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        $resp = Http::timeout(10)->retry(2, 100)->get($this->endpoint);

        if ($resp->failed()) {
            Log::error('OrderImporter: fetch failed', ['status' => $resp->status(), 'endpoint' => $this->endpoint]);
            return [];
        }

        $payload = $resp->json();
        Cache::put($this->cacheKey, $payload, 60);
        return $payload;
    }

    public function import(bool $useCache = true)
    {
        $payload = $this->fetch($useCache);
        $imported = 0;

        $orders = [];
        if (isset($payload['orders']) && is_array($payload['orders'])) {
            foreach ($payload['orders'] as $node) {
                if (isset($node['order']) && is_array($node['order'])) {
                    $orders[] = $node['order'];
                }
            }
        }

        if (empty($orders)) {
            Log::warning('OrderImporter: nenhum pedido encontrado no payload', ['sample' => array_slice((array)$payload, 0, 5)]);
            return 0;
        }

        foreach ($orders as $orderData) {
            DB::beginTransaction();
            try {
                $externalId = $orderData['id'] ?? null;
                if (empty($externalId)) {
                    DB::rollBack();
                    continue;
                }

                $customer = $this->resolveCustomer($orderData);

                $orderModel = $this->upsertOrder($externalId, $orderData, $customer);

                $this->syncItems($orderModel, $orderData['line_items'] ?? []);
                $this->syncRefunds($orderModel, $orderData['refunds'] ?? []);

                if ($orderModel) {
                    $orderModel->save();
                }

                DB::commit();
                $imported++;
                Log::info('OrderImporter: pedido importado', ['external_id' => (string)$externalId, 'order_id' => $orderModel->id ?? null]);
            } catch (Throwable $e) {
                DB::rollBack();
                Log::error('OrderImporter: erro ao importar pedido', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'sample_id' => $this->orderIdSample($orderData),
                ]);
            }
        }

        return $imported;
    }

    protected function resolveCustomer(array $orderData)
    {
        $cust = $orderData['customer'] ?? null;
        if (is_array($cust) && !empty($cust['id'])) {
            return Customer::firstOrCreate(
                ['external_id' => (string)$cust['id']],
                [
                    'first_name' => $cust['first_name'] ?? $cust['name'] ?? null,
                    'last_name' => $cust['last_name'] ?? null,
                    'email' => $cust['email'] ?? $orderData['email'] ?? null,
                    'city' => $cust['city'] ?? null,
                    'state' => $cust['province'] ?? $cust['state'] ?? null,
                ]
            );
        }

        if (!empty($orderData['customer_id'])) {
            return Customer::firstOrCreate(['external_id' => (string)$orderData['customer_id']]);
        }

        return null;
    }

    protected function upsertOrder($externalId, array $orderData, $customer = null)
    {
        return Order::updateOrCreate(
            ['external_id' => (string)$externalId],
            [
                'customer_id' => $customer?->id,
                'financial_status' => $orderData['financial_status'] ?? $orderData['payment_status'] ?? null,
                'fulfillment_status' => $orderData['fulfillment_status'] ?? $orderData['status_id'] ?? $orderData['status'] ?? null,
                'local_currency_amount' => $this->toDecimal($orderData['local_currency_amount'] ?? $orderData['total_price'] ?? 0),
                'placed_at' => isset($orderData['created_at']) ? Carbon::parse($orderData['created_at']) : null,
            ]
        );
    }

    protected function syncItems(Order $orderModel, array $lineItems)
    {
        $orderModel->items()->delete();

        foreach ($lineItems as $li) {
            $prodExt = $li['product_id'] ?? $li['variant_id'] ?? $li['sku'] ?? $li['id'] ?? null;
            $prod = null;
            if ($prodExt) {
                $prod = Product::firstOrCreate(
                    ['external_id' => (string)$prodExt],
                    ['name' => $li['name'] ?? $li['title'] ?? null]
                );
            }

            $orderModel->items()->create([
                'product_id' => $prod?->id,
                'name' => $li['name'] ?? $li['title'] ?? null,
                'quantity' => intval($li['quantity'] ?? 1),
                'local_currency_item_total_price' => $this->toDecimal($li['local_currency_item_total_price'] ?? $li['local_currency_item_price'] ?? $li['total_price'] ?? $li['price'] ?? 0),
                'is_refunded' => !empty($li['is_refunded']) ? true : false,
                'variant_title' => $li['variant_title'] ?? null,
            ]);
        }
    }

    protected function syncRefunds(Order $orderModel, array $refunds)
    {
        $orderModel->refunds()->delete();
        foreach ($refunds as $r) {
            Refund::create([
                'order_id' => $orderModel->id,
                'total_amount' => $this->toDecimal($r['total_amount'] ?? $r['amount'] ?? 0),
                'reason' => $r['reason'] ?? null,
            ]);
        }
    }

    protected function toDecimal($value)
    {
        if (is_null($value)) return 0;
        if (is_numeric($value)) return number_format((float)$value, 2, '.', '');

        $v = (string)$value;
        $v = preg_replace('/[^0-9,\.\-]/', '', $v);
        if (strpos($v, ',') !== false && strpos($v, '.') === false) {
            $v = str_replace(',', '.', $v);
        } else {
            $v = str_replace(',', '', $v);
        }

        return number_format((float)$v, 2, '.', '');
    }

    protected function orderIdSample($orderData)
    {
        return $orderData['id'] ?? $orderData['order_id'] ?? $orderData['number'] ?? null;
    }
}
