<?php

namespace Lawepham\Geoip;

use Lawepham\Geoip\Services\NekudoGeoIpSerivce;
use Lawepham\Geoip\Services\Ip2LocationDbService;
use Illuminate\Support\ServiceProvider;

class LaweGeoIpProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Acelle\Library\Contracts\GeoIpInterface', function ($app) {
            
            // Find table contains location information
            $tables = \DB::select('SHOW TABLES LIKE "ip2location_%"');
            if (count($tables)) {
                return new Ip2LocationDbService();
            } else {
                return new NekudoGeoIpSerivce();
            }
        });
        
        $this->app->bind('geoip', 'Acelle\Library\Contracts\GeoIpInterface');
    }
}
