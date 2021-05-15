<?php
namespace Lawepham\Geoip\Services;

class Ip2LocationDbService
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
        if (!(strpos($ip, ':') > -1)) {
            // Code for IPv4 address $ip_address...
            $table = table('ip2location_db11');
        } else {
            // Code for IPv6 address $ip_address...ip2location_db11_ipv6
            $table = table('ip2location_db11_ipv6');
        }
        
        $tables = \DB::select('SHOW TABLES LIKE "' . $table . '"');
        // Local service
        if (count($tables)) {
            // Check for ipv4 or ipv6
            if (!(strpos($ip, ':') > -1)) {
                $aton = $ip;
                $records = \DB::select('SELECT * FROM `' . $table . '` WHERE INET_ATON(?) <= ip_to LIMIT 1', [$aton]);
            } else {
                $aton = Dot2LongIPv6($ip);
                $records = \DB::select('SELECT * FROM `' . $table . '` WHERE ? <= ip_to LIMIT 1', [$aton]);
            }
            
            if (count($records)) {
                $record = $records[0];
                $this->countryCode = $record->country_code;
                $this->countryName = $record->country_name;
                $this->regionName = $record->region_name;
                $this->city = $record->city_name;
                $this->zipcode = $record->zip_code;
                $this->latitude = $record->latitude;
                $this->longitude = $record->longitude;
            } else {
                throw new \Exception("IP address [$ip] can not be found in local database: " . $table);
            }
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
