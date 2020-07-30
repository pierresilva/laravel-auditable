<?php

namespace pierresilva\Auditable;

use Illuminate\Support\ServiceProvider;

class AuditableServiceProvider extends ServiceProvider
{
    /**
     * Boot the revision service provider.
     *
     * Assigns the migrations to be publishable.
     */
    public function boot()
    {
        $timestamp = date('Y_m_d_His', time());
        $this->publishes([
            __DIR__ . '/../migrations/create_auditable_log_table.php' => database_path("/migrations/{$timestamp}_create_auditable_log_table.php"),
        ], 'migrations');
    }

    /**
     * Register the revision service provider.
     */
    public function register()
    {
        //
    }
}
