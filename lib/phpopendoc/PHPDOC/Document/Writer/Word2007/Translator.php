<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Document\Writer\Word2007;

/**
 * Translator static class that provides various translations for converting
 * points into inches, twips, etc.
 *
 * twip         (dxa)
 * point        (pt)
 * half-point   (hp)
 * milimeters   (mm)
 * centimeters  (cm)
 * pixel        (px)
 * Fiftieths%   (th)
 *
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
abstract class Translator
{

    public static $DPI  = 72;

    const CM_PER_INCH   = 2.54;

    const EMU_PER_INCH  = 914400;
    const EMU_PER_CM    = 360000;
    const EMU_PER_PIXEL = 9535;

    private static $units = array(
        'px'    => 'pixel',
        'in'    => 'inch',
        'pt'    => 'point',
    );

    public static function setDPI($dpi)
    {
        self::$DPI = $dpi;
    }

    /**
     * Intercept calls for dynamic unit conversions.
     *
     * Static function to intercept calls in the format of:
     *      unit2unit()
     *      unitToUnit()
     * For example:
     *      pt2twip(10)
     */
    //public static function __callStatic($name, $arg)
    //{
    //    // @todo Implement __callStatic()
    //}

    /**
     * Return the "twips" of the value given. The type of unit to convert is
     * guessed based on the value given.
     *
     * The unit guesser works best if you append the unit type to the string.
     * For example: 100px, 8.5", 11in, 100cm.
     *
     * Values that do not have a unit suffix are guessed as:
     *      floats are assumed to be inches.
     *
     * @param mixed  $value   The unit value to translate.
     * @param string $default Default unit type to use (if not able to guess)
     */
    public static function twips($value, $unit = 'pt')
    {
        // @todo implement twips()
    }

    /**
     * Convert the value in "Half-Point Size".
     *
     * Half points are 1/144 of an inch.
     */
    public static function pointToHalfPoint($pt)
    {
        return $pt * 2;
    }

    public static function halfPointToPoint($pt)
    {
        return $pt / 2;
    }

    /**
     * Convert the value in "Twentieth of a point" (Twips).
     *
     * Twips are 1/1440 of an inch.
     *
     */
    public static function pointToTwip($pt)
    {
        return $pt * 20;
    }

    public static function twipToPoint($pt)
    {
        return $pt / 20;
    }

    public static function twipToInch($dxa)
    {
        return $dxa / self::$DPI / 20;
    }

    public static function inchToTwip($in)
    {
        return $in * self::$DPI * 20;
    }

    public static function inchToPoint($in)
    {
        return $in * self::$DPI;
    }

    public static function pointToInch($pt)
    {
        return $pt / self::$DPI;
    }

    /**
     * This is just a rough (and bad estimate)
     */
    public static function pixelToTwip($px)
    {
        return $px * 15;
    }

    public static function pixelToCM($px)
    {
        return $px * self::CM_PER_INCH / self::$DPI;
    }

    public static function cmToPixel($cm)
    {
        return $cm / self::CM_PER_INCH * self::$DPI;
    }

    public static function pixelToEMU($px)
    {
        return $px * self::EMU_PER_PIXEL;
    }

    public static function inchToEMU($in)
    {
        return $in * self::EMU_PER_INCH;
    }

    public static function cmToEMU($cm)
    {
        return $cm * self::EMU_PER_CM;
    }

    public static function mmToEMU($mm)
    {
        return $mm * self::EMU_PER_CM / 10;
    }

    public static function pointToEMU($pt)
    {
        return $pt * self::EMU_PER_INCH / self::$DPI;
    }

    public static function twipToEMU($dxa)
    {
        return $dxa * self::EMU_PER_INCH / self::$DPI / 20;
    }

}
