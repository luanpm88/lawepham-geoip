<?php
namespace Lawepham\Geoip\Services;

use Acelle\Library\Contracts\GeoIpInterface;
use SQLite3;
use Exception;
  
class SqliteGeoIp extends SQLite3 implements GeoIpInterface
{
    const DB_FILE_NAME = 'ip2locationdb11.db';
    
    protected $connection;
    protected $dbpath;
    public $sourceUrl;
    protected $sourceHash;
    
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
        $this->open($this->dbpath);
        if (!$this) {
            echo $this->lastErrorMsg();
        } else {
            // echo "Opened database successfully\n";
        }
    }
    
    /**
     * Resolve location information from ip
     *
     * @param String $ip
     * @return void
     */
    public function resolveIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {    
            $ipno = $this->dot2LongIpv4($ip);
            $table = 'v4';
        } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipno = $this->dot2LongIpv6($ip);
            $table = 'v6';
        } else {
            $ipno = null;
        }
                
        $result = $this->query("SELECT * FROM {$table} WHERE {$ipno} <= ip_to LIMIT 1");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->countryCode = $row['country_code'];
            $this->countryName = $row['country_name'];
            $this->regionName = $row['region_name'];
            $this->city = $row['city_name'];
            $this->zipcode = $row['zip_code'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
        }
        $this->close();
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
     * Generate a LONG INT for a given IPv4 address
     * 
     * @param String $ipV4
     * @return LongInt $ipno
     */
    private function dot2LongIpv4($ip) {
        if ($ip == "" || is_null($ip)) {
            return 0; // a specicial location
        } else {
            $ips = explode(".", $ip);
            return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
        }
    }
    
    /**
     * Generate a LONG INT for a given IPv6 address
     * 
     * @param String $ipV6
     * @return LongInt $ipno
     */
    private function dot2LongIpv6($ip) {
        $int = inet_pton($ip);
        $bits = 15;
        $ipv6long = 0;
        while($bits >= 0){
            $bin = sprintf("%08b", (ord($int[$bits])));
            if($ipv6long){
                $ipv6long = $bin . $ipv6long;
            } else {
                $ipv6long = $bin;
            }
            $bits--;
        }
        $ipv6long = gmp_strval(gmp_init($ipv6long, 2), 10);
        return $ipv6long;
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
