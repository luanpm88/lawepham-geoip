<?php
namespace Lawepham\Geoip\Facades;

use Illuminate\Support\Facades\Facade;

class LaweGeoipFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'geoip';
    }
}
