<?php
namespace Lawepham\Geoip\Services;

use Acelle\Library\Contracts\GeoIpInterface;
use Exception;
use GeoIp2\Database\Reader;
  
class SqliteGeoIp implements GeoIpInterface
{
    protected $reader;
    public $sourceUrl;
    public $sourceHash;
    public $dbpath;

    protected $ip;
    protected $countryCode;
    protected $countryName;
    protected $regionName;
    protected $city;
    protected $zipcode;
    protected $latitude;
    protected $longitude;
    
    /**
     * Construction
     *
     */
    public function __construct($dbpath)
    {
        $this->dbpath = $dbpath;
        // Do not initialize the $reader object here
        // as the $dbpath file might not be present
        // Then initialization of the object is for using the $this->setup() method
    }
    
    /**
     * Resolve location information from ip
     *
     * @param String $ip
     * @return void
     */
    public function resolveIp($ip)
    {
        if (is_null($this->reader)) {
            $this->reader = new Reader($this->dbpath);
        }

        $record = $this->reader->city($ip);

        $this->countryCode = $record->country->isoCode;
        $this->countryName = $record->country->name;

        // Get the first subdivision (US only, for states)
        $subdivisions = $record->subdivisions;
        if (!empty($subdivisions)) {
            $subdivision = $subdivisions[0];
            $this->regionName = $subdivision->name;
        } else {
            $this->regionName = $record->city->name;
        }

        $this->city = $record->city->name;
        $this->zipcode = $record->postal->code;
        $this->latitude = $record->location->latitude;
        $this->longitude = $record->location->longitude;
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
    
    /**
     * Set up the service, download the database file
     *
     */
    public function setup()
    {
        if (file_exists($this->dbpath)) {
            if ($this->isValid()) {
                // already set up
                return;
            } else {
                // LaravelLog::info('Invalid db, reloading...');
            }
        }
        
        $downloadUrl = $this->getRedirectFinalTarget($this->sourceUrl);
        set_time_limit(0);
        $fp = fopen($this->dbpath, 'w+');
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $downloadUrl);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_TIMEOUT, 3600);
        curl_setopt($curlSession, CURLOPT_FILE, $fp); 
        curl_setopt($curlSession, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($curlSession);
        
        $error = curl_error($curlSession);
        curl_close($curlSession);
        fclose($fp);

        if (!empty($error)) {
            throw new Exception("Error downloading GeoIP database: " . $error);
        }
        
        if (!$this->isValid()) {
            throw new Exception("Invalid database signature");
        }
    }
    
    /**
     * Set Source URL and Source MD5 Hash
     * 
     * @param String $url
     * @param String $hash
     * @return void
     */
    public function setSource($url, $hash = null)
    {
        $this->sourceUrl = $url;
        $this->sourceHash = $hash;
    }
    
    /**
     * Check if the source hash does match the current source database file
     * 
     * @return Boolean $isValid
     */
    public function isValid()
    {
        return ($this->getDbFileHash() == $this->sourceHash);
    }
    
    /**
     * Get the database file's MD5 hash
     * 
     * @return String $hash
     */
    private function getDbFileHash()
    {
        if (!file_exists($this->dbpath)) {
            return null;
        }
        return hash_file('md5', $this->dbpath);
    }
    
    /**
     * Get the actual download URL or the given URL (follow 301, 302 redirects)
     * 
     * @param String $url
     * @return String $finalUrl
     */
    public function getRedirectFinalTarget($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // set referer on redirect
        curl_exec($ch);
        $target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($target) {
            return $target;
        } else {
            throw new Exception('Cannot resolve GeoIP source\'s download link');
        }
    }
}
