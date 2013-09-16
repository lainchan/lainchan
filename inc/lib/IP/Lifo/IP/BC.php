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
 * BCMath helper class.
 *
 * Provides a handful of BCMath routines that are not included in the native
 * PHP library.
 *
 * Note: The Bitwise functions operate on fixed byte boundaries. For example,
 * comparing the following numbers uses X number of bits:
 *  0xFFFF and 0xFF will result in comparison of 16 bits.
 *  0xFFFFFFFF and 0xF will result in comparison of 32 bits.
 *  etc...
 *
 */
abstract class BC
{
    // Some common (maybe useless) constants
    const MAX_INT_32   = '2147483647';                                  // 7FFFFFFF
    const MAX_UINT_32  = '4294967295';                                  // FFFFFFFF
    const MAX_INT_64   = '9223372036854775807';                         // 7FFFFFFFFFFFFFFF
    const MAX_UINT_64  = '18446744073709551615';                        // FFFFFFFFFFFFFFFF
    const MAX_INT_96   = '39614081257132168796771975167';               // 7FFFFFFFFFFFFFFFFFFFFFFF
    const MAX_UINT_96  = '79228162514264337593543950335';               // FFFFFFFFFFFFFFFFFFFFFFFF
    const MAX_INT_128  = '170141183460469231731687303715884105727';     // 7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
    const MAX_UINT_128 = '340282366920938463463374607431768211455';     // FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

    /**
     * BC Math function to convert a HEX string into a DECIMAL
     */
    public static function bchexdec($hex)
    {
        if (strlen($hex) == 1) {
            return hexdec($hex);
        }

        $remain = substr($hex, 0, -1);
        $last = substr($hex, -1);
        return bcadd(bcmul(16, self::bchexdec($remain), 0), hexdec($last), 0);
    }

    /**
     * BC Math function to convert a DECIMAL string into a BINARY string
     */
    public static function bcdecbin($dec, $pad = null)
    {
        $bin = '';
        while ($dec) {
            $m = bcmod($dec, 2);
            $dec = bcdiv($dec, 2, 0);
            $bin = abs($m) . $bin;
        }
        return $pad ? sprintf("%0{$pad}s", $bin) : $bin;
    }

    /**
     * BC Math function to convert a BINARY string into a DECIMAL string
     */
    public static function bcbindec($bin)
    {
        $dec = '0';
        for ($i=0, $j=strlen($bin); $i<$j; $i++) {
            $dec = bcmul($dec, '2', 0);
            $dec = bcadd($dec, $bin[$i], 0);
        }
        return $dec;
    }

    /**
     * BC Math function to convert a BINARY string into a HEX string
     */
    public static function bcbinhex($bin, $pad = 0)
    {
        return self::bcdechex(self::bcbindec($bin));
    }

    /**
     * BC Math function to convert a DECIMAL into a HEX string
     */
    public static function bcdechex($dec)
    {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last, 0), 16, 0);
        return $remain == 0 ? dechex($last) : self::bcdechex($remain) . dechex($last);
    }

    /**
     * Bitwise AND two arbitrarily large numbers together.
     */
    public static function bcand($left, $right)
    {
        $len = self::_bitwise($left, $right);

        $value = '';
        for ($i=0; $i<$len; $i++) {
            $value .= (($left{$i} + 0) & ($right{$i} + 0)) ? '1' : '0';
        }
        return self::bcbindec($value != '' ? $value : '0');
    }

    /**
     * Bitwise OR two arbitrarily large numbers together.
     */
    public static function bcor($left, $right)
    {
        $len = self::_bitwise($left, $right);

        $value = '';
        for ($i=0; $i<$len; $i++) {
            $value .= (($left{$i} + 0) | ($right{$i} + 0)) ? '1' : '0';
        }
        return self::bcbindec($value != '' ? $value : '0');
    }

    /**
     * Bitwise XOR two arbitrarily large numbers together.
     */
    public static function bcxor($left, $right)
    {
        $len = self::_bitwise($left, $right);

        $value = '';
        for ($i=0; $i<$len; $i++) {
            $value .= (($left{$i} + 0) ^ ($right{$i} + 0)) ? '1' : '0';
        }
        return self::bcbindec($value != '' ? $value : '0');
    }

    /**
     * Bitwise NOT two arbitrarily large numbers together.
     */
    public static function bcnot($left, $bits = null)
    {
        $right = 0;
        $len = self::_bitwise($left, $right, $bits);
        $value = '';
        for ($i=0; $i<$len; $i++) {
            $value .= $left{$i} == '1' ? '0' : '1';
        }
        return self::bcbindec($value);
    }

    /**
     * Shift number to the left
     *
     * @param integer $bits Total bits to shift
     */
    public static function bcleft($num, $bits) {
        return bcmul($num, bcpow('2', $bits));
    }

    /**
     * Shift number to the right
     *
     * @param integer $bits Total bits to shift
     */
    public static function bcright($num, $bits) {
        return bcdiv($num, bcpow('2', $bits));
    }

    /**
     * Determine how many bits are needed to store the number rounded to the
     * nearest bit boundary.
     */
    public static function bits_needed($num, $boundary = 4)
    {
        $bits = 0;
        while ($num > 0) {
            $num = bcdiv($num, '2', 0);
            $bits++;
        }
        // round to nearest boundrary
        return $boundary ? ceil($bits / $boundary) * $boundary : $bits;
    }

    /**
     * BC Math function to return an arbitrarily large random number.
     */
    public static function bcrand($min, $max = null)
    {
        if ($max === null) {
            $max = $min;
            $min = 0;
        }

        // swap values if $min > $max
        if (bccomp($min, $max) == 1) {
            list($min,$max) = array($max,$min);
        }

        return bcadd(
            bcmul(
                bcdiv(
                    mt_rand(0, mt_getrandmax()),
                    mt_getrandmax(),
                    strlen($max)
                ),
                bcsub(
                    bcadd($max, '1'),
                    $min
                )
            ),
            $min
        );
    }

    /**
     * Computes the natural logarithm using a series.
     * @author Thomas Oldbury.
     * @license Public domain.
     */
    public static function bclog($num, $iter = 10, $scale = 100)
    {
        $log = "0.0";
        for($i = 0; $i < $iter; $i++) {
            $pow = 1 + (2 * $i);
            $mul = bcdiv("1.0", $pow, $scale);
            $fraction = bcmul($mul, bcpow(bcsub($num, "1.0", $scale) / bcadd($num, "1.0", $scale), $pow, $scale), $scale);
            $log = bcadd($fraction, $log, $scale);
        }
        return bcmul("2.0", $log, $scale);
    }

    /**
     * Computes the base2 log using baseN log.
     */
    public static function bclog2($num, $iter = 10, $scale = 100)
    {
        return bcdiv(self::bclog($num, $iter, $scale), self::bclog("2", $iter, $scale), $scale);
    }

    public static function bcfloor($num)
    {
        if (substr($num, 0, 1) == '-') {
            return bcsub($num, 1, 0);
        }
        return bcadd($num, 0, 0);
    }

    public static function bcceil($num)
    {
        if (substr($num, 0, 1) == '-') {
            return bcsub($num, 0, 0);
        }
        return bcadd($num, 1, 0);
    }

    /**
     * Compare two numbers and return -1, 0, 1 depending if the LEFT number is
     * < = > the RIGHT.
     *
     * @param string|integer $left Left side operand
     * @param string|integer $right Right side operand
     * @return integer Return -1,0,1 for <=> comparison
     */
    public static function cmp($left, $right)
    {
        // @todo could an optimization be done to determine if a normal 32bit
        //       comparison could be done instead of using bccomp? But would
        //       the number verification cause too much overhead to be useful?
        return bccomp($left, $right, 0);
    }

    /**
     * Internal function to prepare for bitwise operations
     */
    private static function _bitwise(&$left, &$right, $bits = null)
    {
        if ($bits === null) {
            $bits = max(self::bits_needed($left), self::bits_needed($right));
        }

        $left  = self::bcdecbin($left);
        $right = self::bcdecbin($right);

        $len   = max(strlen($left), strlen($right), (int)$bits);

        $left  = sprintf("%0{$len}s", $left);
        $right = sprintf("%0{$len}s", $right);

        return $len;
    }

}
