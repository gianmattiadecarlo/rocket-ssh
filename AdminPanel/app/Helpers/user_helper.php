<?php

use Morilog\Jalali\Jalalian;


function getSessionUser()
{
    if (session()->get("userInfo")) {
        return  (array) session()->get('userInfo');
    }

    return false;
}


function viewContentPath($viewFile)
{
    return PATH_APP . DS . "Views" . DS . $viewFile;
}

function loadViewSection($path)
{
    $fullPath = PATH_APP . DS . "Views" . DS . $path;
    if (file_exists($fullPath)) {
        include $fullPath;
    }
}


function inlineIcon($iconName, $customClass = "")
{
    $html = "<i class='far fa-$iconName icon $customClass'></i>";
    return  $html;
}

function getAdminRole()
{
    return  container()->userInfo->role;
}

function getAdminUsername()
{
    return  container()->userInfo->username;
}

function getCurrentDate()
{
    return  jdate()->format("d F Y");
}

function generateNetmodQR($userInfo)
{

    $username   = $userInfo->username;
    $password   = $userInfo->password;
    $serverIp   = servIPAddress();
    $port       = getenv("SSH_PORT");

    $str = "chl=ssh://$username:$password@$serverIp:$port";
    $url = 'https://chart.googleapis.com/chart?cht=qr&chs=160x160&chld=L|0&' . $str;
    return $url;
}

function parseSQLFileForTables($sqlContent, $tableNames = [])
{
    $insertValues       = array();
    $lines              = explode("\n", $sqlContent);
    $isInsideInsert     = false;
    $currentTable       = '';
    $currentInsert      = '';

    foreach ($lines as $line) {
        // Check if the line starts with 'INSERT INTO'
        if (strpos(trim($line), 'INSERT INTO') === 0) {
            $isInsideInsert = true;
            $currentTable   = getTableName($line);
            $currentInsert  = '';
        }

        if ($isInsideInsert && in_array($currentTable, $tableNames)) {
            // Append the line to the current insert statement
            $currentInsert .= $line;

            // Check if the current insert statement is complete
            if (substr(trim($line), -1) === ';') {
                $insertValues[$currentTable] = extractValuesFromQuery($currentInsert);
                $isInsideInsert = false;
            }
        }
    }

    return $insertValues;
}

function getTableName($insertStatement)
{
    // Extract the table name from the insert statement
    $pattern = '/INSERT INTO `([^`]*)`/i';
    preg_match($pattern, $insertStatement, $matches);
    return $matches[1];
}


function extractValuesFromQuery($stringQuery)
{
    $pattern = '/\((.*?)\)/';
    preg_match_all($pattern, $stringQuery, $matches);

    $values = $matches[1];
    $values = array_map('trim', $values);

    // Separate each set of values into its own array
    $insertArrays = [];
    foreach ($values as $value) {
        $insertArrays[] = explode(',', str_replace("'", "", $value));
    }

    return $insertArrays;
}


function convertToEnNum($string)
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    $num = range(0, 9);
    $convertedPersianNums = str_replace($persian, $num, $string);
    $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

    return $englishNumbersOnly;
}

function formatTraffice($traffic)
{
    if ($traffic < 1024) {
        return round($traffic, 2) . " MB";
    }
    return round($traffic / 1024, 3) . " GB";
}


function calcDifferenceDate($startDate, $endDate)
{
    $startDate = new DateTime(date("Y-m-d", $startDate));
    $endDate = new DateTime(date("Y-m-d", $endDate));

    $interval = $startDate->diff($endDate);

    $months = $interval->m;
    $days = $interval->d;

    $result = '';

    if ($months > 0) {
        $result .= "$months ";
        if ($days > 0) {
            $result .= "ماه و ";
        } else {
            $result .= "ماهه";
        }
    }

    if ($days > 0) {
        $result .= "$days ";
        if ($months > 0) {
            $result .= " روز";
        } else {
            $result .= "روزه";
        }
    }

    return trim($result);
}


function getEndOfDate($date)
{
    if (!is_numeric($date)) {
        $date = strtotime($date);
    }
    $date = strtotime("today", $date);
    return strtotime("tomorrow", $date) - 1;
}

function  getStartOfDate($date)
{
    if (!is_numeric($date)) {
        $date = strtotime($date);
    }
    return  strtotime("today", $date);
}


function userStatusLabel($status)
{
    $arrayStats = [
        "active"            => "فعال",
        "deactive"          => "غیر فعال",
        "expiry_traffic"    => "انقضای ترافیک",
        "expiry_date"       => "انقضای تاریخ",
    ];

    if (!empty($arrayStats[$status])) {
        return $arrayStats[$status];
    }

    return $status;
}


function githubLastVersion()
{
    $url = getConfig("versionUrl");
    if ($url) {
        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );
        $urlContent = @file_get_contents($url, false, $context);
        if (!empty($urlContent)) {
            $urlContent = json_decode($urlContent, true);
            $lastVersion = $urlContent["tag_name"];
            return number_format($lastVersion, '.', 1);
        }
    }
    return false;
}
