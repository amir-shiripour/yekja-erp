<?php

namespace Modules\ResellerHub\App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;
use Morilog\Jalali\Jalalian;
use Throwable;

final class JalaliDateHelper
{
    /**
     * Convert any date representation to a Jalalian instance.
     */
    public static function toJalali(mixed $date): ?Jalalian
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof Jalalian) {
            return $date;
        }

        if ($date instanceof Carbon && $date->year < 1900) {
            return new Jalalian($date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second);
        }

        if ($date instanceof DateTimeInterface) {
            return Jalalian::fromCarbon(Carbon::instance($date));
        }

        if (is_string($date)) {
            try {
                return Jalalian::fromCarbon(Carbon::parse($date));
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get the current Jalalian now instance.
     */
    public static function now(): Jalalian
    {
        return Jalalian::now();
    }

    /**
     * Get the current Jalali year.
     */
    public static function currentYear(): int
    {
        return self::now()->getYear();
    }

    /**
     * Add months to a date respecting the Persian Jalali calendar.
     */
    public static function addMonths(mixed $date, int $months): Carbon
    {
        $baseDate = $date instanceof Carbon ? (clone $date) : (self::parseToGregorian($date) ?? Carbon::now());

        try {
            return Jalalian::fromCarbon($baseDate)->addMonths($months)->toCarbon();
        } catch (Throwable) {
            return $baseDate->addMonths($months);
        }
    }

    /**
     * Format a date into Jalali string (returns null if invalid, optional Persian numerals).
     */
    public static function formatDate(mixed $date, string $format = 'Y/m/d', bool $persianDigits = false): ?string
    {
        $jalali = self::toJalali($date);
        if (!$jalali) {
            return null;
        }

        $formatted = $jalali->format($format);
        return $persianDigits ? ResellerHelper::toPersianDigits($formatted) : $formatted;
    }

    /**
     * Format a date into Jalali string with Persian numerals.
     */
    public static function formatJalali(mixed $date, string $format = 'Y/m/d', bool $persianDigits = true): string
    {
        $jalali = self::toJalali($date);
        if (!$jalali) {
            return '-';
        }

        $formatted = $jalali->format($format);
        return $persianDigits ? ResellerHelper::toPersianDigits($formatted) : $formatted;
    }

    /**
     * Calculate remaining days between today and the target date (negative if past).
     */
    public static function daysRemaining(mixed $date): ?int
    {
        if (!$date) {
            return null;
        }

        try {
            $target = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
            $today = Carbon::today();
            return (int)$today->diffInDays($target->startOfDay(), false);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Return a localized Persian text describing remaining days or expiry status.
     */
    public static function humanRemaining(mixed $date): string
    {
        $diff = self::daysRemaining($date);
        if ($diff === null) {
            return '-';
        }

        if ($diff < 0) {
            $abs = abs($diff);
            return 'منقضی شده (' . ResellerHelper::toPersianDigits($abs) . ' روز پیش)';
        }

        if ($diff === 0) {
            return 'امروز منقضی می‌شود';
        }

        if ($diff === 1) {
            return 'فردا منقضی می‌شود';
        }

        return ResellerHelper::toPersianDigits($diff) . ' روز مانده';
    }

    /**
     * Replace any Gregorian date patterns in a text string with localized Persian Jalali dates.
     */
    public static function formatGregorianInTextToJalali(?string $text): string
    {
        if (empty($text) || !is_string($text)) {
            return (string)$text;
        }

        return (string)preg_replace_callback('/\b(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}:\d{2}(?::\d{2})?))?\b/', function ($m) {
            try {
                $c = Carbon::parse($m[0]);
                $j = Jalalian::fromCarbon($c);
                $res = !empty($m[4]) ? $j->format('Y/m/d H:i') : $j->format('Y/m/d');
                return ResellerHelper::toPersianDigits($res);
            } catch (Throwable) {
                return $m[0];
            }
        }, $text);
    }

    /**
     * Parse any Jalali or Gregorian date to a Gregorian Carbon instance.
     */
    public static function toCarbon(mixed $dateStr, ?string $format = null): ?Carbon
    {
        return self::parseToGregorian($dateStr, $format);
    }

    /**
     * Parse any Jalali or Gregorian date string/object to a Gregorian Carbon instance.
     * Single authoritative implementation for Jalali date parsing (SSOT).
     */
    public static function parseToGregorian(mixed $dateStr, ?string $format = null): ?Carbon
    {
        if (empty($dateStr)) {
            return null;
        }

        if ($dateStr instanceof Carbon) {
            return $dateStr;
        }

        if ($dateStr instanceof DateTimeInterface) {
            return Carbon::instance($dateStr);
        }

        if ($dateStr instanceof Jalalian) {
            return $dateStr->toCarbon();
        }

        $cleaned = trim(ResellerHelper::toEnglishDigits((string)$dateStr));
        $normalized = str_replace('/', '-', $cleaned);

        if (preg_match('/^(13|14)\d{2}[-\/]\d{1,2}[-\/]\d{1,2}(?:\s+\d{1,2}:\d{1,2}(?::\d{1,2})?)?$/', $cleaned)) {
            try {
                $parts = preg_split('/[\s]+/', $normalized);
                $dateParts = explode('-', $parts[0]);
                $year = (int)$dateParts[0];
                $month = (int)$dateParts[1];
                $day = (int)$dateParts[2];

                $hour = 0;
                $minute = 0;
                $second = 0;
                if (!empty($parts[1])) {
                    $timeParts = explode(':', $parts[1]);
                    $hour = (int)($timeParts[0] ?? 0);
                    $minute = (int)($timeParts[1] ?? 0);
                    $second = (int)($timeParts[2] ?? 0);
                }

                return (new Jalalian($year, $month, $day, $hour, $minute, $second))->toCarbon();
            } catch (Throwable) {
                // fallback to Carbon::parse below
            }
        }

        try {
            return Carbon::parse($normalized);
        } catch (Throwable) {
            return null;
        }
    }
}
