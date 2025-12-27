<?php

namespace App\Helpers;

class TimezoneHelper
{
    /**
     * Common airport timezone mappings.
     * In production, use a complete database or API.
     */
    protected static $airportTimezones = [
        // Philippines
        'MNL' => 'Asia/Manila',
        'CEB' => 'Asia/Manila',
        'DVO' => 'Asia/Manila',
        'ILO' => 'Asia/Manila',
        'CRK' => 'Asia/Manila',
        
        // Asia
        'SIN' => 'Asia/Singapore',
        'HKG' => 'Asia/Hong_Kong',
        'NRT' => 'Asia/Tokyo',
        'ICN' => 'Asia/Seoul',
        'BKK' => 'Asia/Bangkok',
        'KUL' => 'Asia/Kuala_Lumpur',
        'CGK' => 'Asia/Jakarta',
        'TPE' => 'Asia/Taipei',
        'PVG' => 'Asia/Shanghai',
        'PEK' => 'Asia/Shanghai',
        'DEL' => 'Asia/Kolkata',
        'BOM' => 'Asia/Kolkata',
        
        // USA
        'LAX' => 'America/Los_Angeles',
        'SFO' => 'America/Los_Angeles',
        'JFK' => 'America/New_York',
        'ORD' => 'America/Chicago',
        'DFW' => 'America/Chicago',
        'ATL' => 'America/New_York',
        'MIA' => 'America/New_York',
        'SEA' => 'America/Los_Angeles',
        'LAS' => 'America/Los_Angeles',
        'PHX' => 'America/Phoenix',
        'DEN' => 'America/Denver',
        'IAH' => 'America/Chicago',
        
        // Europe
        'LHR' => 'Europe/London',
        'CDG' => 'Europe/Paris',
        'FRA' => 'Europe/Berlin',
        'AMS' => 'Europe/Amsterdam',
        'MAD' => 'Europe/Madrid',
        'FCO' => 'Europe/Rome',
        'IST' => 'Europe/Istanbul',
        'DXB' => 'Asia/Dubai',
        'DOH' => 'Asia/Qatar',
        
        // Australia
        'SYD' => 'Australia/Sydney',
        'MEL' => 'Australia/Melbourne',
        'BNE' => 'Australia/Brisbane',
        'PER' => 'Australia/Perth',
    ];

    /**
     * Get timezone for airport code.
     * 
     * @param string $airportCode IATA airport code
     * @return string Timezone identifier
     */
    public static function getTimezoneForAirport(string $airportCode): string
    {
        $code = strtoupper($airportCode);
        return self::$airportTimezones[$code] ?? 'UTC';
    }

    /**
     * Convert time to UTC from airport timezone.
     * 
     * @param string $time Time string
     * @param string $airportCode IATA airport code
     * @return \Carbon\Carbon
     */
    public static function toUtc(string $time, string $airportCode): \Carbon\Carbon
    {
        $timezone = self::getTimezoneForAirport($airportCode);
        return \Carbon\Carbon::parse($time, $timezone)->utc();
    }

    /**
     * Convert UTC time to airport local time.
     * 
     * @param \Carbon\Carbon $utcTime
     * @param string $airportCode IATA airport code
     * @return \Carbon\Carbon
     */
    public static function toLocal(\Carbon\Carbon $utcTime, string $airportCode): \Carbon\Carbon
    {
        $timezone = self::getTimezoneForAirport($airportCode);
        return $utcTime->copy()->setTimezone($timezone);
    }

    /**
     * Format time for display in local timezone.
     * 
     * @param \Carbon\Carbon $utcTime
     * @param string $airportCode IATA airport code
     * @param string $format
     * @return string
     */
    public static function formatLocal(\Carbon\Carbon $utcTime, string $airportCode, string $format = 'M d, Y g:i A'): string
    {
        return self::toLocal($utcTime, $airportCode)->format($format);
    }
}
