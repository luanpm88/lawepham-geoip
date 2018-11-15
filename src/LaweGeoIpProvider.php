<?php

namespace Lawepham\Geoip;

use Lawepham\Geoip\Services\NekudoGeoIpSerivce;
use Lawepham\Geoip\Services\Ip2LocationDbService;
use Lawepham\Geoip\Services\SqliteGeoIp;
use Illuminate\Support\ServiceProvider;
use Acelle\Model\Setting;
use Exception;

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
            
            // Deprecated
            //     $tables = \DB::select('SHOW TABLES LIKE "ip2location_%"');
            //     if (count($tables)) {
            //         return new Ip2LocationDbService();
            //     } else {
            //         return new NekudoGeoIpSerivce();
            //     }
            
            // Always use the
            $engine = Setting::get('geoip.engine');
            $dbname = Setting::get('geoip.sqlite.dbname');
            $dbpath = base_path($dbname);
            $sourceUrl = Setting::get('geoip.sqlite.source_url');
            $sourceHash = Setting::get('geoip.sqlite.source_hash');
            
            switch ($engine) {
                case 'sqlite':
                    $service = new SqliteGeoIp($dbpath);
                    $service->setSource($sourceUrl, $sourceHash);
                    return $service;
                case 'mysql':
                    // no longer supported, left here for reference only
                    return new Ip2LocationDbService();
                case 'nekudo':
                    // no longer supported, left here for reference only
                    return new NekudoGeoIpSerivce();
                default:
                    throw new Exception('No GeoIp database supported');
            }
        });
    }
}
