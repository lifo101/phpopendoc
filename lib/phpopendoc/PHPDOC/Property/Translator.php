<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Property;

/**
 * Translator static class that provides various translations for converting
 * points into inches, etc.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
abstract class Translator
{

    /**
     * Convert the value in "Half-Point Size".
     *
     * Half points are 1/144 of an inch.
     */
    public static function pointToHalfPoint($points)
    {
        return round($points * 2);
    }
    
    public static function halfPointToPoint($points)
    {
        return round($points / 2);
    }

    /**
     * Convert the value in "Twentith of a point" (Twips).
     *
     * Twips are 1/1440 of an inch.
     *
     */
    public static function pointToTwip($points)
    {
        return round($points * 20);
    }

    public static function TwipToPoint($points)
    {
        return round($points / 20);
    }
}
