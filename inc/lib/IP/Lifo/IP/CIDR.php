<?php
/**
 * This file is part of the Lifo\IP PHP Library.
 *
 * (c) Jason Morriss <lifo2013@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lifo\IP;

/**
 * CIDR Block helper class.
 *
 * Most routines can be used statically or by instantiating an object and
 * calling its methods.
 *
 * Provides routines to do various calculations on IP addresses and ranges.
 * Convert to/from CIDR to ranges, etc.
 */
class CIDR
{
    const INTERSECT_NO              = 0;
    const INTERSECT_YES             = 1;
    const INTERSECT_LOW             = 2;
    const INTERSECT_HIGH            = 3;

    protected $start;
    protected $end;
    protected $prefix;
    protected $version;
    protected $istart;
    protected $iend;

    private $cache;

    /**
     * Create a new CIDR object.
     *
     * The IP range can be arbitrary and does not have to fall on a valid CIDR
     * range. Some methods will return different values depending if you ignore
     * the prefix or not. By default all prefix sensitive methods will assume
     * the prefix is used.
     *
     * @param string $cidr An IP address (1.2.3.4), CIDR block (1.2.3.4/24),
     *                     or range "1.2.3.4-1.2.3.10"
     * @param string $end Ending IP in range if no cidr/prefix is given
     */
    public function __construct($cidr, $end = null)
    {
        if ($end !== null) {
            $this->setRange($cidr, $end);
        } else {
            $this->setCidr($cidr);
        }
    }

    /**
     * Returns the string representation of the CIDR block.
     */
    public function __toString()
    {
        // do not include the prefix if its a single IP
        try {
            if ($this->isTrueCidr() && (
                ($this->version == 4 and $this->prefix != 32) ||
                ($this->version == 6 and $this->prefix != 128)
                )
            ) {
                return $this->start . '/' . $this->prefix;
            }
        } catch (\Exception $e) {
            // isTrueCidr() calls getRange which can throw an exception
        }
        if (strcmp($this->start, $this->end) == 0) {
            return $this->start;
        }
        return $this->start . ' - ' . $this->end;
    }

    public function __clone()
    {
        // do not clone the cache. No real reason why. I just want to keep the
        // memory foot print as low as possible, even though this is trivial.
        $this->cache = array();
    }

    /**
     * Set an arbitrary IP range.
     * The closest matching prefix will be calculated but the actual range
     * stored in the object can be arbitrary.
     * @param string $start Starting IP or combination "start-end" string.
     * @param string $end   Ending IP or null.
     */
    public function setRange($ip, $end = null)
    {
        if (strpos($ip, '-') !== false) {
            list($ip, $end) = array_map('trim', explode('-', $ip, 2));
        }

        if (false === filter_var($ip, FILTER_VALIDATE_IP) ||
            false === filter_var($end, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid IP range \"$ip-$end\"");
        }

        // determine version (4 or 6)
        $this->version = (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? 6 : 4;

        $this->istart = IP::inet_ptod($ip);
        $this->iend   = IP::inet_ptod($end);

        // fix order
        if (bccomp($this->istart, $this->iend) == 1) {
            list($this->istart, $this->iend) = array($this->iend, $this->istart);
            list($ip, $end) = array($end, $ip);
        }

        $this->start = $ip;
        $this->end = $end;

        // calculate real prefix
        $len = $this->version == 4 ? 32 : 128;
        $this->prefix = $len - strlen(BC::bcdecbin(BC::bcxor($this->istart, $this->iend)));
    }

    /**
     * Returns true if the current IP is a true cidr block
     */
    public function isTrueCidr()
    {
        return $this->start == $this->getNetwork() && $this->end == $this->getBroadcast();
    }

    /**
     * Set the CIDR block.
     *
     * The prefix length is optional and will default to 32 ot 128 depending on
     * the version detected.
     *
     * @param string $cidr CIDR block string, eg: "192.168.0.0/24" or "2001::1/64"
     * @throws \InvalidArgumentException If the CIDR block is invalid
     */
    public function setCidr($cidr)
    {
        if (strpos($cidr, '-') !== false) {
            return $this->setRange($cidr);
        }

        list($ip, $bits) = array_pad(array_map('trim', explode('/', $cidr, 2)), 2, null);
        if (false === filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid IP address \"$cidr\"");
        }

        // determine version (4 or 6)
        $this->version = (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? 6 : 4;

        $this->start = $ip;
        $this->istart = IP::inet_ptod($ip);

        if ($bits !== null and $bits !== '') {
            $this->prefix = $bits;
        } else {
            $this->prefix = $this->version == 4 ? 32 : 128;
        }

        if (($this->prefix < 0)
            || ($this->prefix > 32 and $this->version == 4)
            || ($this->prefix > 128 and $this->version == 6)) {
            throw new \InvalidArgumentException("Invalid IP address \"$cidr\"");
        }

        $this->end = $this->getBroadcast();
        $this->iend = IP::inet_ptod($this->end);

        $this->cache = array();
    }

    /**
     * Get the IP version. 4 or 6.
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the prefix.
     *
     * Always returns the "proper" prefix, even if the IP range is arbitrary.
     *
     * @return integer
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Return the starting presentational IP or Decimal value.
     *
     * Ignores prefix
     */
    public function getStart($decimal = false)
    {
        return $decimal ? $this->istart : $this->start;
    }

    /**
     * Return the ending presentational IP or Decimal value.
     *
     * Ignores prefix
     */
    public function getEnd($decimal = false)
    {
        return $decimal ? $this->iend : $this->end;
    }

    /**
     * Return the next presentational IP or Decimal value (following the
     * broadcast address of the current CIDR block).
     */
    public function getNext($decimal = false)
    {
        $next = bcadd($this->getEnd(true), '1');
        return $decimal ? $next : new self(IP::inet_dtop($next));
    }

    /**
     * Returns true if the IP is an IPv4
     *
     * @return boolean
     */
    public function isIPv4()
    {
        return $this->version == 4;
    }

    /**
     * Returns true if the IP is an IPv6
     *
     * @return boolean
     */
    public function isIPv6()
    {
        return $this->version == 6;
    }

    /**
     * Get the cidr notation for the subnet block.
     *
     * This is useful for when you want a string representation of the IP/prefix
     * and the starting IP is not on a valid network boundrary (eg: Displaying
     * an IP from an interface).
     *
     * @return string IP in CIDR notation "ipaddr/prefix"
     */
    public function getCidr()
    {
        return $this->start . '/' . $this->prefix;
    }

    /**
     * Get the [low,high] range of the CIDR block
     *
     * Prefix sensitive.
     *
     * @param boolean $ignorePrefix If true the arbitrary start-end range is
     *                              returned. default=false.
     */
    public function getRange($ignorePrefix = false)
    {
        $range = $ignorePrefix
            ? array($this->start, $this->end)
            : self::cidr_to_range($this->start, $this->prefix);
        // watch out for IP '0' being converted to IPv6 '::'
        if ($range[0] == '::' and strpos($range[1], ':') == false) {
            $range[0] = '0.0.0.0';
        }
        return $range;
    }

    /**
     * Return the IP in its fully expanded form.
     *
     * For example: 2001::1 == 2007:0000:0000:0000:0000:0000:0000:0001
     *
     * @see IP::inet_expand
     */
    public function getExpanded()
    {
        return IP::inet_expand($this->start);
    }

    /**
     * Get network IP of the CIDR block
     *
     * Prefix sensitive.
     *
     * @param boolean $ignorePrefix If true the arbitrary start-end range is
     *                              returned. default=false.
     */
    public function getNetwork($ignorePrefix = false)
    {
        // micro-optimization to prevent calling getRange repeatedly
        $k = $ignorePrefix ? 1 : 0;
        if (!isset($this->cache['range'][$k])) {
            $this->cache['range'][$k] = $this->getRange($ignorePrefix);
        }
        return $this->cache['range'][$k][0];
    }

    /**
     * Get broadcast IP of the CIDR block
     *
     * Prefix sensitive.
     *
     * @param boolean $ignorePrefix If true the arbitrary start-end range is
     *                              returned. default=false.
     */
    public function getBroadcast($ignorePrefix = false)
    {
        // micro-optimization to prevent calling getRange repeatedly
        $k = $ignorePrefix ? 1 : 0;
        if (!isset($this->cache['range'][$k])) {
            $this->cache['range'][$k] = $this->getRange($ignorePrefix);
        }
        return $this->cache['range'][$k][1];
    }

    /**
     * Get the network mask based on the prefix.
     *
     */
    public function getMask()
    {
        return self::prefix_to_mask($this->prefix, $this->version);
    }

    /**
     * Get total hosts within CIDR range
     *
     * Prefix sensitive.
     *
     * @param boolean $ignorePrefix If true the arbitrary start-end range is
     *                              returned. default=false.
     */
    public function getTotal($ignorePrefix = false)
    {
        // micro-optimization to prevent calling getRange repeatedly
        $k = $ignorePrefix ? 1 : 0;
        if (!isset($this->cache['range'][$k])) {
            $this->cache['range'][$k] = $this->getRange($ignorePrefix);
        }
        return bcadd(bcsub(IP::inet_ptod($this->cache['range'][$k][1]),
                           IP::inet_ptod($this->cache['range'][$k][0])), '1');
    }

    public function intersects($cidr)
    {
        return self::cidr_intersect((string)$this, $cidr);
    }

    /**
     * Determines the intersection between an IP (with optional prefix) and a
     * CIDR block.
     *
     * The IP will be checked against the CIDR block given and will either be
     * inside or outside the CIDR completely, or partially.
     *
     * NOTE: The caller should explicitly check against the INTERSECT_*
     * constants because this method will return a value > 1 even for partial
     * matches.
     *
     * @param mixed $ip The IP/cidr to match
     * @param mixed $cidr The CIDR block to match within
     * @return integer Returns an INTERSECT_* constant
     * @throws \InvalidArgumentException if either $ip or $cidr is invalid
     */
    public static function cidr_intersect($ip, $cidr)
    {
        // use fixed length HEX strings so we can easily do STRING comparisons
        // instead of using slower bccomp() math.
        list($lo,$hi)   = array_map(function($v){ return sprintf("%032s", IP::inet_ptoh($v)); }, CIDR::cidr_to_range($ip));
        list($min,$max) = array_map(function($v){ return sprintf("%032s", IP::inet_ptoh($v)); }, CIDR::cidr_to_range($cidr));

        /** visualization of logic used below
            lo-hi   = $ip to check
            min-max = $cidr block being checked against
            --- --- --- lo  --- --- hi  --- --- --- --- --- IP/prefix to check
            --- min --- --- max --- --- --- --- --- --- --- Partial "LOW" match
            --- --- --- --- --- min --- --- max --- --- --- Partial "HIGH" match
            --- --- --- --- min max --- --- --- --- --- --- No match "NO"
            --- --- --- --- --- --- --- --- min --- max --- No match "NO"
            min --- max --- --- --- --- --- --- --- --- --- No match "NO"
            --- --- min --- --- --- --- max --- --- --- --- Full match "YES"
         */

        // IP is exact match or completely inside the CIDR block
        if ($lo >= $min and $hi <= $max) {
            return self::INTERSECT_YES;
        }

        // IP is completely outside the CIDR block
        if ($max < $lo or $min > $hi) {
            return self::INTERSECT_NO;
        }

        // @todo is it useful to return LOW/HIGH partial matches?

        // IP matches the lower end
        if ($max <= $hi and $min <= $lo) {
            return self::INTERSECT_LOW;
        }

        // IP matches the higher end
        if ($min >= $lo and $max >= $hi) {
            return self::INTERSECT_HIGH;
        }

        return self::INTERSECT_NO;
    }

    /**
     * Converts an IPv4 or IPv6 CIDR block into its range.
     *
     * @todo May not be the fastest way to do this.
     *
     * @static
     * @param string       $cidr CIDR block or IP address string.
     * @param integer|null $bits If /bits is not specified on string they can be
     *                           passed via this parameter instead.
     * @return array             A 2 element array with the low, high range
     */
    public static function cidr_to_range($cidr, $bits = null)
    {
        if (strpos($cidr, '/') !== false) {
            list($ip, $_bits) = array_pad(explode('/', $cidr, 2), 2, null);
        } else {
            $ip = $cidr;
            $_bits = $bits;
        }

        if (false === filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("IP address \"$cidr\" is invalid");
        }

        // force bit length to 32 or 128 depending on type of IP
        $bitlen = (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? 128 : 32;

        if ($bits === null) {
            // if no prefix is given use the length of the binary string which
            // will give us 32 or 128 and result in a single IP being returned.
            $bits = $_bits !== null ? $_bits : $bitlen;
        }

        if ($bits > $bitlen) {
            throw new \InvalidArgumentException("IP address \"$cidr\" is invalid");
        }

        $ipdec = IP::inet_ptod($ip);
        $ipbin = BC::bcdecbin($ipdec, $bitlen);

        // calculate network
        $netmask = BC::bcbindec(str_pad(str_repeat('1',$bits), $bitlen, '0'));
        $ip1 = BC::bcand($ipdec, $netmask);

        // calculate "broadcast" (not technically a broadcast in IPv6)
        $ip2 = BC::bcor($ip1, BC::bcnot($netmask));

        return array(IP::inet_dtop($ip1), IP::inet_dtop($ip2));
    }

    /**
     * Return the CIDR string from the range given
     */
    public static function range_to_cidr($start, $end)
    {
        $cidr = new CIDR($start, $end);
        return (string)$cidr;
    }

    /**
     * Return the maximum prefix length that would fit the IP address given.
     *
     * This is useful to determine how my bit would be needed to store the IP
     * address when you don't already have a prefix for the IP.
     *
     * @example 216.240.32.0 would return 27
     *
     * @param string $ip IP address without prefix
     * @param integer $bits Maximum bits to check; defaults to 32 for IPv4 and 128 for IPv6
     */
    public static function max_prefix($ip, $bits = null)
    {
        static $mask = array();

        $ver = (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? 6 : 4;
        $max = $ver == 6 ? 128 : 32;
        if ($bits === null) {
            $bits = $max;

        }

        $int = IP::inet_ptod($ip);
        while ($bits > 0) {
            // micro-optimization; calculate mask once ...
            if (!isset($mask[$ver][$bits-1])) {
                // 2^$max - 2^($max - $bits);
                if ($ver == 4) {
                    $mask[$ver][$bits-1] = pow(2, $max) - pow(2, $max - ($bits-1));
                } else {
                    $mask[$ver][$bits-1] = bcsub(bcpow(2, $max), bcpow(2, $max - ($bits-1)));
                }
            }

            $m = $mask[$ver][$bits-1];
            //printf("%s/%d: %s & %s == %s\n", $ip, $bits-1, BC::bcdecbin($m, 32), BC::bcdecbin($int, 32), BC::bcdecbin(BC::bcand($int, $m)));
            //echo "$ip/", $bits-1, ": ", IP::inet_dtop($m), " ($m) & $int == ", BC::bcand($int, $m), "\n";
            if (bccomp(BC::bcand($int, $m), $int) != 0) {
                return $bits;
            }
            $bits--;
        }
        return $bits;
    }

    /**
     * Return a contiguous list of true CIDR blocks that span the range given.
     *
     * Note: It's not a good idea to call this with IPv6 addresses. While it may
     * work for certain ranges this can be very slow. Also an IPv6 list won't be
     * as accurate as an IPv4 list.
     *
     * @example
     *  range_to_cidrlist(192.168.0.0, 192.168.0.15) ==
     *    192.168.0.0/28
     *  range_to_cidrlist(192.168.0.0, 192.168.0.20) ==
     *    192.168.0.0/28
     *    192.168.0.16/30
     *    192.168.0.20/32
     */
    public static function range_to_cidrlist($start, $end)
    {
        $ver   = (false === filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? 6 : 4;
        $start = IP::inet_ptod($start);
        $end   = IP::inet_ptod($end);

        $len = $ver == 4 ? 32 : 128;
        $log2 = $ver == 4 ? log(2) : BC::bclog(2);

        $list = array();
        while (BC::cmp($end, $start) >= 0) { // $end >= $start
            $prefix = self::max_prefix(IP::inet_dtop($start), $len);
            if ($ver == 4) {
                $diff = $len - floor( log($end - $start + 1) / $log2 );
            } else {
                // this is not as accurate due to the bclog function
                $diff = bcsub($len, BC::bcfloor(bcdiv(BC::bclog(bcadd(bcsub($end, $start), '1')), $log2)));
            }

            if ($prefix < $diff) {
                $prefix = $diff;
            }

            $list[] = IP::inet_dtop($start) . "/" . $prefix;

            if ($ver == 4) {
                $start += pow(2, $len - $prefix);
            } else {
                $start = bcadd($start, bcpow(2, $len - $prefix));
            }
        }
        return $list;
    }

    /**
     * Return an list of optimized CIDR blocks by collapsing adjacent CIDR
     * blocks into larger blocks.
     *
     * @param array $cidrs List of CIDR block strings or objects
     * @param integer $maxPrefix Maximum prefix to allow
     * @return array Optimized list of CIDR objects
     */
    public static function optimize_cidrlist($cidrs, $maxPrefix = 32)
    {
        // all indexes must be a CIDR object
        $cidrs = array_map(function($o){ return $o instanceof CIDR ? $o : new CIDR($o); }, $cidrs);
        // sort CIDR blocks in proper order so we can easily loop over them
        $cidrs = self::cidr_sort($cidrs);

        $list = array();
        while ($cidrs) {
            $c = array_shift($cidrs);
            $start = $c->getStart();

            $max = bcadd($c->getStart(true), $c->getTotal());

            // loop through each cidr block until its ending range is more than
            // the current maximum.
            while (!empty($cidrs) and $cidrs[0]->getStart(true) <= $max) {
                $b = array_shift($cidrs);
                $newmax = bcadd($b->getStart(true), $b->getTotal());
                if ($newmax > $max) {
                    $max = $newmax;
                }
            }

            // add the new cidr range to the optimized list
            $list = array_merge($list, self::range_to_cidrlist($start, IP::inet_dtop(bcsub($max, '1'))));
        }

        return $list;
    }

    /**
     * Sort the list of CIDR blocks, optionally with a custom callback function.
     *
     * @param array $cidrs A list of CIDR blocks (strings or objects)
     * @param Closure $callback Optional callback to perform the sorting.
     *                          See PHP usort documentation for more details.
     */
    public static function cidr_sort($cidrs, $callback = null)
    {
        // all indexes must be a CIDR object
        $cidrs = array_map(function($o){ return $o instanceof CIDR ? $o : new CIDR($o); }, $cidrs);

        if ($callback === null) {
            $callback = function($a, $b) {
                if (0 != ($o = BC::cmp($a->getStart(true), $b->getStart(true)))) {
                    return $o;  // < or >
                }
                if ($a->getPrefix() == $b->getPrefix()) {
                    return 0;
                }
                return $a->getPrefix() < $b->getPrefix() ? -1 : 1;
            };
        } elseif (!($callback instanceof \Closure) or !is_callable($callback)) {
            throw new \InvalidArgumentException("Invalid callback in CIDR::cidr_sort, expected Closure, got " . gettype($callback));
        }

        usort($cidrs, $callback);
        return $cidrs;
    }

    /**
     * Return the Prefix bits from the IPv4 mask given.
     *
     * This is only valid for IPv4 addresses since IPv6 addressing does not
     * have a concept of network masks.
     *
     * Example: 255.255.255.0 == 24
     *
     * @param string $mask IPv4 network mask.
     */
    public static function mask_to_prefix($mask)
    {
        if (false === filter_var($mask, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException("Invalid IP netmask \"$mask\"");
        }
        return strrpos(IP::inet_ptob($mask, 32), '1') + 1;
    }

    /**
     * Return the network mask for the prefix given.
     *
     * Normally this is only useful for IPv4 addresses but you can generate a
     * mask for IPv6 addresses as well, only because its mathematically
     * possible.
     *
     * @param integer $prefix CIDR prefix bits (0-128)
     * @param integer $version IP version. If null the version will be detected
     *                         based on the prefix length given.
     */
    public static function prefix_to_mask($prefix, $version = null)
    {
        if ($version === null) {
            $version = $prefix > 32 ? 6 : 4;
        }
        if ($prefix < 0 or $prefix > 128) {
            throw new \InvalidArgumentException("Invalid prefix length \"$prefix\"");
        }
        if ($version != 4 and $version != 6) {
            throw new \InvalidArgumentException("Invalid version \"$version\". Must be 4 or 6");
        }

        if ($version == 4) {
            return long2ip($prefix == 0 ? 0 : (0xFFFFFFFF >> (32 - $prefix)) << (32 - $prefix));
        } else {
            return IP::inet_dtop($prefix == 0 ? 0 : BC::bcleft(BC::bcright(BC::MAX_UINT_128, 128-$prefix), 128-$prefix));
        }
    }

    /**
     * Return true if the $ip given is a true CIDR block.
     *
     * A true CIDR block is one where the $ip given is the actual Network
     * address and broadcast matches the prefix appropriately.
     */
    public static function cidr_is_true($ip)
    {
        $ip = new CIDR($ip);
        return $ip->isTrueCidr();
    }
}
