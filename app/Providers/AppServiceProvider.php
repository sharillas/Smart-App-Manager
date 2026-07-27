<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Orcamento;
use App\Observers\OrcamentoObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Orcamento::observe(OrcamentoObserver::class);
    }
}
