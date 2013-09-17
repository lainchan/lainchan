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
 * IP Address helper class.
 *
 * Provides routines to translate IPv4 and IPv6 addresses between human readable
 * strings, decimal, hexidecimal and binary.
 *
 * Requires BCmath extension and IPv6 PHP support
 */
abstract class IP
{
    /**
     * Convert a human readable (presentational) IP address string into a decimal string.
     */
    public static function inet_ptod($ip)
    {
        // shortcut for IPv4 addresses
        if (strpos($ip, ':') === false && strpos($ip, '.') !== false) {
            return sprintf('%u', ip2long($ip));
        }

        // remove any cidr block notation
        if (($o = strpos($ip, '/')) !== false) {
            $ip = substr($ip, 0, $o);
        }

        // unpack into 4 32bit integers
        $parts = unpack('N*', inet_pton($ip));
        foreach ($parts as &$part) {
            if ($part < 0) {
                // convert signed int into unsigned
                $part = sprintf('%u', $part);
                //$part = bcadd($part, '4294967296');
            }
        }

        // add each 32bit integer to the proper bit location in our big decimal
        $decimal = $parts[4];                                                           // << 0
        $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));                     // << 32
        $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));           // << 64
        $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));  // << 96

        return $decimal;
    }

    /**
     * Convert a decimal string into a human readable IP address.
     */
    public static function inet_dtop($decimal, $expand = false)
    {
        $parts = array();
        $parts[1] = bcdiv($decimal,                  '79228162514264337593543950336', 0);   // >> 96
        $decimal  = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
        $parts[2] = bcdiv($decimal,                  '18446744073709551616', 0);            // >> 64
        $decimal  = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
        $parts[3] = bcdiv($decimal,                  '4294967296', 0);                      // >> 32
        $decimal  = bcsub($decimal, bcmul($parts[3], '4294967296'));
        $parts[4] = $decimal;                                                               // >> 0

        foreach ($parts as &$part) {
            if (bccomp($part, '2147483647') == 1) {
                $part = bcsub($part, '4294967296');
            }
            $part = (int) $part;
        }

        // if the first 96bits is all zeros then we can safely assume we
        // actually have an IPv4 address. Even though it's technically possible
        // you're not really ever going to see an IPv6 address in the range:
        // ::0 - ::ffff
        // It's feasible to see an IPv6 address of "::", in which case the
        // caller is going to have to account for that on their own.
        if (($parts[1] | $parts[2] | $parts[3]) == 0) {
            $ip = long2ip($parts[4]);
        } else {
            $packed = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
            $ip = inet_ntop($packed);
        }

        // Turn IPv6 to IPv4 if it's IPv4
        if (preg_match('/^::\d+\./', $ip)) {
            return substr($ip, 2);
        }

        return $expand ? self::inet_expand($ip) : $ip;
    }

    /**
     * Convert a human readable (presentational) IP address into a HEX string.
     */
    public static function inet_ptoh($ip)
    {
        return bin2hex(inet_pton($ip));
        //return BC::bcdechex(self::inet_ptod($ip));
    }

    /**
     * Convert a human readable (presentational) IP address into a BINARY string.
     */
    public static function inet_ptob($ip, $bits = 128)
    {
        return BC::bcdecbin(self::inet_ptod($ip), $bits);
    }

    /**
     * Convert a binary string into an IP address (presentational) string.
     */
    public static function inet_btop($bin)
    {
        return self::inet_dtop(BC::bcbindec($bin));
    }

    /**
     * Convert a HEX string into a human readable (presentational) IP address
     */
    public static function inet_htop($hex)
    {
        return self::inet_dtop(BC::bchexdec($hex));
    }

    /**
     * Expand an IP address. IPv4 addresses are returned as-is.
     *
     * Example:
     *      2001::1     expands to 2001:0000:0000:0000:0000:0000:0000:0001
     *      ::127.0.0.1 expands to 0000:0000:0000:0000:0000:0000:7f00:0001
     *      127.0.0.1   expands to 127.0.0.1
     */
    public static function inet_expand($ip)
    {
        // strip possible cidr notation off
        if (($pos = strpos($ip, '/')) !== false) {
            $ip = substr($ip, 0, $pos);
        }
        $bytes = unpack('n*', inet_pton($ip));
        if (count($bytes) > 2) {
            return implode(':', array_map(function ($b) {
                return sprintf("%04x", $b);
            }, $bytes));
        }
        return $ip;
    }

    /**
     * Convert an IPv4 address into an IPv6 address.
     *
     * One use-case for this is IP 6to4 tunnels used in networking.
     *
     * @example
     *      to_ipv4("10.10.10.10") == a0a:a0a
     *
     * @param string $ip IPv4 address.
     * @param boolean $mapped If true a Full IPv6 address is returned within the
     *                        official ipv4to6 mapped space "0:0:0:0:0:ffff:x:x"
     */
    public static function to_ipv6($ip, $mapped = false)
    {
        if (!self::isIPv4($ip)) {
            throw new \InvalidArgumentException("Invalid IPv4 address \"$ip\"");
        }

        $num = IP::inet_ptod($ip);
        $o1 = dechex($num >> 16);
        $o2 = dechex($num & 0x0000FFFF);

        return $mapped ? "0:0:0:0:0:ffff:$o1:$o2" : "$o1:$o2";
    }

    /**
     * Returns true if the IP address is a valid IPv4 address
     */
    public static function isIPv4($ip)
    {
        return $ip === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Returns true if the IP address is a valid IPv6 address
     */
    public static function isIPv6($ip)
    {
        return $ip === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Compare two IP's (v4 or v6) and return -1, 0, 1 if the first is < = >
     * the second.
     *
     * @param string $ip1 IP address
     * @param string $ip2 IP address to compare against
     * @return integer Return -1,0,1 depending if $ip1 is <=> $ip2
     */
    public static function cmp($ip1, $ip2)
    {
        return bccomp(self::inet_ptod($ip1), self::inet_ptod($ip2), 0);
    }
}
