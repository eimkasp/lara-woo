<?php

namespace App\Providers;

use Filament\PluginServiceProvider;
use Filament\Pages\Page;
use App\Filament\Pages\Dashboard;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends PluginServiceProvider
{
    public function registerPages(): void
    {
        Page::register([
            Dashboard::class,
        ]);
    }
}
