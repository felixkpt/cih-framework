<?php

use App\Models\Core\ExportImportLog;
use App\Models\User;
use Cih\Framework\Repositories\StatusRepository;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

if (!function_exists('autoForm')) {
    function autoForm($elements, $action, $classes = [], $model = null)
    {
        $model_form = null;
        if (!is_array($elements)) {
            $model_form = $elements;
            $elements = new $elements();
            $elements = $elements->getfillable();
            $elements['form_model'] = $model_form;
        }
        $formRepository = new \Cih\Framework\Repositories\FormRepository();
        return $formRepository->autoGenerate($elements, $action, $classes, $model);
    }
}

/**
 * Truncate a string after a given number of words -- limit number of words
 */
if (!function_exists('limit_string_words')) {
    function limit_string_words($text, $words_limit)
    {
        if (str_word_count($text, 0) > $words_limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$words_limit]) . ' ...';
        }
        return $text;
    }
}
if (!function_exists('sizeFilter')) {

    function sizeFilter($bytes)
    {
        $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
        return (round($bytes, 2) . " " . $label[$i]);
    }
}

/**
 * Get ticket age
 */
if (!function_exists('getTicketAge')) {
    function getTicketAge($ticket_id)
    {
        $ticket = \App\Models\Core\Ticket::whereId($ticket_id)->first();
        if ($ticket) {
            $date_from = $ticket->created_at;
            if (!$date_from)
                return "";
            //            $date_to = Carbon::now();
            $date_to = $ticket->updated_at;
            if ($ticket->closed_at) {
                $date_to = $ticket->closed_at;
            } else {
                $date_to = Carbon::now();
            }

            return getTimeDifferenceInDaysAndHours($date_from, $date_to);
        }
        return "";
    }
}

if (!function_exists('getTimeDifferenceInDaysAndHours')) {
    function getTimeDifferenceInDaysAndHours($date_from, $date_to, $formated = false)
    {
        if (!$date_from)
            return 0;
        if ($date_to)
            $date_to = Carbon::parse($date_to);
        else
            $date_to = Carbon::today();
        $date_from = Carbon::parse($date_from);
        $div_pre = '<strong class="text-info">';
        $days = $date_from->diffInDays($date_to);
        $hours = $date_from->copy()->addDays($days)->diffInHours($date_to);
        $minutes = $date_from->copy()->addDays($days)->addHours($hours)->diffInMinutes($date_to);
        $days_string = ($days == 1) ? $days . 'Day ' : $days . 'Days ';
        $hours_string = ($hours == 1) ? $hours . 'Hr ' : $hours . 'Hrs ';

        if ($days == 0)
            $days_string = "";
        if ($hours == 0)
            $hours_string = "";
        return $div_pre . $days_string . $hours_string . $minutes . "Mins " . '</strong>';
    }
}
// Get Ticket Age

/**
 * Get current logged in user
 */
if (!function_exists('getUser')) {
    function getUser($user_id = null)
    {
        if ($user_id)
            return \App\User::whereId($user_id)->first();
        return auth()->user();
    }
}

if (!function_exists('getUserStatus')) {
    function getUserStatus($state)
    {
        return StatusRepository::getUserStatus($state);
    }
}

if (!function_exists('getTicketStatus')) {
    function getTicketStatus($state)
    {
        return StatusRepository::getTicketStatus($state);
    }
}


if (!function_exists('getTicketStatusButton')) {
    function getTicketStatusButton($state, $ticket_id = null, $icon = true)
    {
        $state = $state == 0 ? 1 : $state;

        $stat_index = getTicketStatus($state);
        if (is_string($stat_index))
            $stat_index = strtolower($stat_index);
        $stat_index = is_string($stat_index) ? strtolower($stat_index) : $stat_index;

        $btn['open'] = [0 => 'danger', 1 => 'clock', 2 => ' Open'];
        $btn['resolved'] = [0 => 'primary', 1 => 'hourglass', 2 => ' Resolved'];
        $btn['closed'] = [0 => 'success', 1 => 'thumbs-up', 2 => ' Closed'];

        $btn_class = isset($btn[$stat_index][0]) ? $btn[$stat_index][0] : @$btn[3][0];
        $status_name = isset($btn[$stat_index][2]) ? $btn[$stat_index][2] : @$btn[2][0];

        return "<a href=\"javascript:void(0)\" class=\"font-weight-bold font-14 mb-0 text-" . $btn_class . " \"><span><i class='fas fa-" . @$btn[$stat_index][1] . "'></i>  " . $status_name . "</span></a>";

        //        if (!$icon)
        //            return "<button href=\"#more_ticket_info_modal\" onclick=\"getMoreDetails(' . $ticket_id . ')\" data-toggle=\"modal\" class=\"btn btn-" . $btn[$stat_index][0] . " badge  badge-pill font-weight-bold btn-sm\">  " . $btn[$stat_index][2] . "</span>";
        //        return "<button href=\"#more_ticket_info_modal\" onclick=\"getMoreDetails(' . $ticket_id . ')\" data-toggle=\"modal\" class=\"btn btn-" . $btn[$stat_index][0] . " badge  badge-pill font-weight-bold btn-sm\"> <i class='fas fa-" . $btn[$stat_index][1] . "'></i>  " . $btn[$stat_index][2] . "</span>";
        //        return "<span class=\"border border-".$btn[$stat_index][0]."   badge-pill text-".$btn[$stat_index][0]." btn-sm\"> <i class='fas fa-".$btn[$stat_index][1]."'></i>  ".$btn[$stat_index][2]."</span>";
    }
}

if (!function_exists('formatTicketStatus')) {
    function formatTicketStatus($ticket)
    {
        $statusClass = [1 => 'fas fa-clock', 2 => 'fas fa-spinner', 3 => 'fas fa-hourglass', 4 => 'fa fa-check', 5 => 'fas fa-thumbs-up', 6 => 'fas fa-hourglass'];
        $color = [1 => 'danger', 2 => 'warning', 3 => 'info', 4 => 'success', 5 => 'success', 6 => 'teal'];
        $btn_color = isset($color[$ticket->ticket_status_id]) ? $color[$ticket->ticket_status_id] : $color[3];
        $btn_class = isset($statusClass[$ticket->ticket_status_id]) ? $statusClass[$ticket->ticket_status_id] : $statusClass[1];
        return '<a href="#"  data-toggle="modal" class="btn badge btn-sm  btn-outline-' . $btn_color . '"><i class="' . $btn_class . '"></i> ' . StatusRepository::getTicketStatus($ticket->ticket_status_id) . '</a>';
    }
}

if (!function_exists('getStaffGenderInitial')) {
    function getStaffGenderInitial($state)
    {
        if ($state == 2)
            return "F";
        return "M";
    }
}


if (!function_exists('userCan')) {
    function userCan($slug)
    {
        return request()->user()->isAllowedTo($slug);
    }
}
if (!function_exists('formatIllnessStateText')) {
    function formatIllnessStateText($state)
    {
        if (!in_array($state, ['no_case', 'mild', 'severe', 'covid19_suspect']))
            $state = 'mild';
        $label['no_case'] = [0 => 'success', 1 => ' No case'];
        $label['mild'] = [0 => 'info', 1 => ' Mild'];
        $label['severe'] = [0 => 'warning', 1 => ' Severe'];
        $label['covid19_suspect'] = [0 => 'danger', 1 => ' Covid19 Suspect'];

        return "<span class=\"font-weight-bolder text-" . $label[$state][0] . " btn-sm\">  " . $label[$state][1] . "</span>";
    }
}

if (!function_exists('userHas')) {
    function userHas($user_id, $slug, $section = 'writing')
    {
        return User::where('id', '=', $user_id)->first()->isAllowedTo($slug, $section);
    }
}
if (!function_exists('formatDeadline')) {
    function formatDeadline($date)
    {

        $date = \Carbon\Carbon::createFromTimestamp(strtotime($date));
        if ($date->isPast()) {
            $div_pre = '<strong class="text-danger">';
            $pre = "(late)";
            $days = $date->diffInDays();
            $hours = $date->copy()->addDays($days)->diffInHours();
            $minutes = $date->copy()->addDays($days)->addHours($hours)->diffInMinutes();
            $days_string = $days . 'D ';
            $hours_string = $hours . "H ";
        } else {
            $pre = '';
            $days = $date->diffInDays();
            $hours = $date->copy()->subDays($days)->diffInHours();
            if ($days > 0 || $hours > 5) {
                $div_pre = '<strong class="text-success">';
            } else {
                $div_pre = '<strong class="text-warning">';
            }
            $minutes = $date->copy()->subDays($days)->subHour($hours)->diffInMinutes();
            $days_string = $days . 'D ';
            $hours_string = $hours . "H ";
        }
        if ($days == 0)
            $days_string = "";
        if ($hours == 0)
            $hours_string = "";
        return $div_pre . $days_string . $hours_string . $minutes . "Mins " . $pre . '</strong>';
    }
}

if (!function_exists('formatDeadline1')) {
    function formatDeadline1($date)
    {
        $date = \Carbon\Carbon::createFromTimestamp(strtotime($date));
        if ($date->isPast())
            $div_pre = '<strong class="text-danger">';
        else
            $div_pre = '<strong class="text-success">';

        return $div_pre . $date->isoFormat('ddd, Do MMM Y') . '</strong>';
    }
}

if (!function_exists('getDaysDifference')) {
    function getDaysDifference($date_from, $date_to, $formated = false)
    {
        if (!$date_from)
            return 0;
        if ($date_to)
            $date_to = Carbon::parse($date_to);
        else
            $date_to = Carbon::today();

        $date_from = Carbon::parse($date_from);
        if ($date_to < $date_from)
            return 0;
        $date_diff = $date_to->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $date_from);

        if (!$formated)
            return $date_diff;
        else
            if ($date_diff > 0)
            $div_pre = '<strong class="text-danger">';
        else
            $div_pre = '<strong class="text-success">';

        return $div_pre . number_format($date_diff) . '</strong>';
    }
}

if (!function_exists('getInitials')) {

    function getInitials($string): ?string
    {
        $words = explode(" ", $string);
        $initials = null;
        foreach ($words as $word) {
            $initials .= strtoupper(@$word[0]);
        }
        return Str::substr($initials, 0, 2);
    }
}

if (!function_exists('getHostDomain')) {
    /**
     * get the site address
     * domain
     */
    function getHostDomain($address)
    {
        $address = stripcslashes($address);
        $address = trim($address, '"');
        $parseUrl = parse_url(trim($address));
        if (!isset($parseUrl['host']))
            //            dd(stripslashes($address), stripcslashes($address), $parseUrl);
            $domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
        return str_ireplace('www.', '', $domain);
    }
}


if (!function_exists('featureData')) {
    function featureData()
    {
        $feature_data = [];
        if (Storage::exists('features.json')) // check if the json file exists
            $feature_data = Storage::get('features.json');
        if ($feature_data) {
            $feature_data = json_decode($feature_data, true); // if exists fetch the details
        } else {
            $feature_data = [];
            $ip_toggle_status = 0;
            $feature_data['ip_toggle_status'] = $ip_toggle_status;
            Storage::put('features.json', json_encode($feature_data));
        }
        return $feature_data;
    }
}


if (!function_exists('getActivityReminderPeriod')) {
    function getActivityReminderPeriod($state)
    {
        return StatusRepository::getActivityReminderPeriod($state);
    }
}


if (!function_exists('formatPhone')) {

    function formatPhone($phone)
    {
        $len = strlen($phone);

        if ($len == 0) {
            return $phone;
        }

        $begin = substr($phone, 0, 1);

        if ($begin == "+") {

            if ($len == 13) {
                return substr($phone, 1);
            }

            if ($len == 11 || $len == 10 || $len == 12 | $len == 13) {
                return $phone;
            }
        }

        if ($begin == "0") {
            return '254' . substr($phone, 1);
        }


        if ($len == 9 && ($begin == "7" || $begin == "1")) {
            return '254' . $phone;
        }

        if ($len == 11 || $len == 10 || $len == 12 | $len == 13) {
            return $phone;
        }

        return $phone;
    }
}

if (!function_exists('isPhoneValid')) {

    function isPhoneValid($phone)
    {
        $len = strlen($phone);

        if ($len < 9) {
            return false;
        }

        $begin = substr($phone, 0, 1);

        if ($begin == "+") {
            if ($len == 11 || $len == 10 || $len == 12 | $len == 13) {
                return true;
            }
        }

        if ($len == 9 && ($begin == "7" || $begin == "1")) {
            return true;
        }

        if ($len == 11 || $len == 10 || $len == 12 | $len == 13) {
            return true;
        }

        return false;
    }
}


if (!function_exists('obfuscateEmail')) {

    function obfuscateEmail($email)
    {
        $em = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = floor(strlen($name) / 2);

        return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
    }
}

if (!function_exists('obfuscatePhone')) {

    function obfuscatePhone($phone)
    {
        if (empty($phone)) {
            return 'N/A';
        }

        $len = floor(strlen($phone) / 2);

        return substr($phone, 0, $len) . str_repeat('*', $len);
    }
}

if (!function_exists('getNameInitials')) {
    function getNameInitials($name = null)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        if (count($words) == 1) {
            preg_match_all('#([A-Z]+)#', $name, $capitals);

            if (count($capitals[1]) >= 2) {
                return substr(implode('', $capitals[1]), 0, 2);
            }
            return strtoupper(substr($name, 0, 2));
        }
        return "";
    }
}
if (!function_exists('titleCase')) {

    function titleCase($string): ?string
    {
        return ucwords(strtolower($string));
    }
}

if (!function_exists('randColor')) {

    function randColor()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}


if (!function_exists('isWindows')) {
    function isWindows()
    {
        $is_windows = false;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $is_windows = true;
        }
        return $is_windows;
    }

    if (!function_exists('removeNonReadableChars')) {
        function removeNonReadableChars($textStr)
        {
            // Removes Non-Readable characters
            $textStr = preg_replace("/[^a-zA-Z0-9`_.,;@#%~'\"\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-\s\\\\]+/", "", trim($textStr));

            return trim($textStr);
        }
    }

    if (!function_exists('removeWhiteSpaces')) {
        function removeWhiteSpaces($str)
        {
            $str = trim($str);
            $new_str = preg_replace("/\s+/", "", $str);
            return @$new_str;
        }
    }

    if (!function_exists('removePlusInPhoneNumb')) {
        function removePlusInPhoneNumb($phone_num_str)
        {
            if (strlen(trim($phone_num_str)) > 0) {
                $first_char = substr($phone_num_str, 0, 1);
                if ($first_char === "+")
                    $phone_num_str = preg_replace('/^[' . $first_char . ']/', '', $phone_num_str); // upgrade to ltrim since PHP trim appears to remove too much quite often ie ltrim('254572021425','254') -->res = 72021425 instead of 572021425
            }

            return @$phone_num_str;
        }
    }

    if (!function_exists('removePhoneNoPrefixStr')) {
        function removePhoneNoPrefixStr($phone_num_str)
        {
            $phone_num_str = str_replace(' ', '', $phone_num_str);
            $phone_num_str = removeNonReadableChars($phone_num_str);
            $phone_num_str = removeWhiteSpaces($phone_num_str);
            $phone_num_str = removePlusInPhoneNumb($phone_num_str);
            $original_phone_num = $phone_num_str;

            $prefix = "";
            if (strlen(trim($phone_num_str)) > 0) {
                $first_char = substr($phone_num_str, 0, 1);
                $first_three_char = substr($phone_num_str, 0, 3);

                if ($first_char === "0")
                    $prefix = "0";
                elseif ($first_three_char === "254")
                    $prefix = "254";

                if (strlen(trim($prefix)) > 0) {
                    $phone_num_str = preg_replace('/^' . $prefix . '/', '', $phone_num_str); // upgrade to ltrim since PHP trim appears to remove too much quite often ie ltrim('254572021425','254') -->res = 72021425 instead of 572021425
                    if (strlen($phone_num_str) > 8)
                        $phone_num_str = $phone_num_str; //"0" . $phone_num_str
                    else
                        $phone_num_str = $original_phone_num;
                }
            }

            return trim(@$phone_num_str);
        }
    }
}

if (!function_exists('getSessionDateFilterKey')) {
    function getSessionDateFilterKey()
    {
        return 'date_filters_' . request()->user()->id;
    }
}

if (!function_exists('bytesToHuman')) {

    function bytesToHuman($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('storeExportImportLogs')) {
    function storeExportImportLogs($file_name = null, $description = null, $log_type = null)
    {
        $exportlog = new ExportImportLog();
        $exportlog->exported_file = $file_name;
        $exportlog->log_type = $log_type;
        $exportlog->user_id = auth()->id();
        $exportlog->ip_address = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
        $exportlog->export_description = $description;
        //        $exportlog->export_description = auth()->user()->name." exported Training Feedback on ".Carbon::now()->toDayDateTimeString();
        $exportlog->save();
        return 0;
    }
}

if (!function_exists('genderMapping')) {
    function genderMapping($genderInt = null)
    {
        $genderMapping = [
            1 => "Male",
            2 => "Female",
            3 => "Other"
        ];

        return @$genderMapping[$genderInt];
    }
}
