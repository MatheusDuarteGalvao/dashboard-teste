<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use App\Services\OrderImporter;

class ImportOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:import-orders {--no-cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa pedidos da API CRM para o banco local';

    /**
     * Execute the console command.
     */
    public function handle(OrderImporter $importer): int
    {
        $useCache = !$this->option('no-cache');
        $this->info('Importando pedidos...');
        $count = $importer->import($useCache);
        $this->info("Importados: {$count}");
        return 0;
    }
}
