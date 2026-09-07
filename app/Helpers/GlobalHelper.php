<?php

namespace App\Helpers;
use App\Notifications\TelegramError;
use Illuminate\Support\Facades\Notification;

class GlobalHelper
{
    public static function findString($needle,$haystack,$i,$word)
    {   // $i should be "" or "i" for case insensitive
        if (strtoupper($word)=="W") {   // if $word is "W" then word search instead of string in string search.
            if (preg_match("/\b{$needle}\b/{$i}", $haystack)) {
                return true;
            }
        } else {
            if(preg_match("/{$needle}/{$i}", $haystack)) {
                return true;
            }
        }
        return false;
        // Put quotes around true and false above to return them as strings instead of as bools/ints.
    }

    public static function dayEngToInd($english) {
        if ($english == 'Monday') {
            $day = 'Senin';
        } else if ($english == 'Tuesday') {
            $day = 'Selasa';
        } else if ($english == 'Wednesday') {
            $day = 'Rabu';
        } else if ($english == 'Thursday') {
            $day = 'Kamis';
        } else if ($english == 'Friday') {
            $day = 'Jum\'at';
        } else if ($english == 'Saturday') {
            $day = 'Sabtu';
        } else if ($english == 'Sunday') {
            $day = 'Minggu';
        } else {
            $day = 'Unknown';
        }

        return $day;
    }

    public static function numberToMonthIndo($number) {
        if ($number == '01') {
            $month = 'Januari';
        } else if ($number == '02') {
            $month = 'Februari';
        } else if ($number == '03') {
            $month = 'Maret';
        } else if ($number == '04') {
            $month = 'April';
        } else if ($number == '05') {
            $month = 'Mei';
        } else if ($number == '06') {
            $month = 'Juni';
        } else if ($number == '07') {
            $month = 'Juli';
        } else if ($number == '08') {
            $month = 'Agustus';
        } else if ($number == '09') {
            $month = 'September';
        } else if ($number == '10') {
            $month = 'Oktober';
        } else if ($number == '11') {
            $month = 'November';
        } else if ($number == '12') {
            $month = 'Desember';
        }

        return $month;
    }

    public static function minutes($time)
    {
        $time = explode(':', $time);
        return ($time[0]*60) + ($time[1]) + ($time[2]/60);
    }

    public static function minuteToHourMinute($minutes) 
    {
        $hours = floor($minutes / 60);
        $min = $minutes - ($hours * 60);

        return $hours." jam, ".$min." menit";
    }

    public static function convertSeparator($number, $separator = '.')
    {
        $number = str_replace($separator, '', $number);

        if ($number > 0) {
            return floatval($number);
        }

        return 0;
    }

    public static function periodDateTime($date, $dateTo = null)
    {
        if ($dateTo) {
            $ages_interval = date_diff(date_create($dateTo), date_create($date));
        } else {
            $ages_interval = date_diff(date_create(), date_create($date));
        }
        $age = $ages_interval->format("%Y thn, %M bln, %d hr");

        return $age;
    }

    public static function dateIndo($date) 
    {
        if ($date) {
            $expl_time = explode(' ', $date);

            $fullDate = explode('-', $expl_time[0]);

            $date = $fullDate[2];
            $month = $fullDate[1];
            $year = $fullDate[0];

            return $date.' '.self::numberToMonthIndo($month).' '.$year.' '.(isset($expl_time[1]) ? $expl_time[1] : '');
        }

        return '-';
    }

    public static function findArrayByValue($params, $key, $value)
    {
        $res = false;

        if ($params) {
            foreach ($params as $param) {
                if ($param[$key] == $value) {
                    $res = true;
                    continue;
                }
            }
        }

        return $res;
    }

    public static function camelToSnake($camel)
    {
        $snake = preg_replace('/[A-Z]/', '_$0', $camel);
        $snake = strtolower($snake);
        $snake = ltrim($snake, '_');
        return $snake;
    }

    public static function getClientIP()
    {
        $ip = 'Unknown';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ip_address = explode(',', $ip);
        return $ip_address[0];
    }

    public static function escapeJsonString($value) 
    {  
        $escapers = ['\n'];
        $replacements = [", "];
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }
    
    public static function pushLog($type,$request,$response,$trace=[]) 
    {
        $isAPILimit = false;
        if(isset($response['error_message']['error'])) {
            $error = $response['error_message']['error'];
            $isAPILimit = isset($error['detail']['api_rate_limit']);
        }

        if($type == 'error') {
            $trace_split = [];

            for ($i=0; $i<4; $i++) {
                if (isset($trace['#0'.$i])) {
                    $trace_split['#0'.$i] = $trace['#0'.$i];
                }
            }

            $encoded = json_encode(utf8ize(array_merge($request, $response, ['trace' => $trace_split])));

            \Log::error($encoded);
            if (!env('APP_DEBUG')) {
                Notification::route('telegram', env('TELEGRAM_LOGGER_CHAT_ID'))->notify(new TelegramError(['data' => $encoded]));
            }
        } else {
            $encoded = json_encode(array_merge($request, $response));
            \Log::info(self::escapeJsonString($encoded));
        }
    }

    public static function randomText( $length = 8, $type = 'alnum' )
    {
        switch ( $type ) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string) $type;
                break;
        }


        $crypto_rand_secure = function ( $min, $max ) {
            $range = $max - $min;
            if ( $range < 0 ) return $min; // not so random...
            $log    = log( $range, 2 );
            $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
            $bits   = (int) $log + 1; // length in bits
            $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ( $rnd >= $range );
            return $min + $rnd;
        };

        $token = "";
        $max   = strlen( $pool );
        for ( $i = 0; $i < $length; $i++ ) {
            $token .= $pool[$crypto_rand_secure( 0, $max )];
        }

        return $token;
    }

    public static function slugify($text, string $divider = '_')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }
}