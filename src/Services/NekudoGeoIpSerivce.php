<?php
namespace Lawepham\Geoip\Services;

use Acelle\Library\Contracts\GeoIpInterface;

class NekudoGeoIpSerivce implements GeoIpInterface
{
    const API_URL = 'http://geoip.nekudo.com/api/';
    protected $ip;
    protected $countryCode;
    protected $countryName;
    protected $regionName;
    protected $city;
    protected $zipcode;
    protected $latitude;
    protected $longitude;
    
    /**
     * Resolve location information from ip
     *
     * @param String $ip
     * @return void
     */
    public function resolveIp($ip)
    {
        $result = file_get_contents(self::API_URL . $ip);
        $values = json_decode($result, true);
        
        if (isset($values["country"])) {
            $this->countryCode = $values["country"]["code"];
            $this->countryName = $values["country"]["name"];
        }
        
        // $location->region_name = $record->region_name;
        
        if (isset($values["city"])) {
            $this->city = $values["city"];
        }
        // $location->zipcode = $record->zip_code;
        
        if (isset($values["location"])) {
            $this->latitude = $values["location"]["latitude"];
            $this->longitude = $values["location"]["longitude"];
        }
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getCountryName()
    {
        return $this->countryName;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getRegionName()
    {
        return $this->regionName;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    /**
     * Get location infomation via ip
     *
     * @param String $ip
     * @return void
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
