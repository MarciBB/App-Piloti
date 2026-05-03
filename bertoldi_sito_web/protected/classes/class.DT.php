<?php

class DT
{
   public static $months = array('short' => array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Sep', 'Oct', 'Nov', 'Dec'),
                                 'full'  => array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'));

   public $info = array();
   private $informat = null;

   public function __construct($date = null, $format = 'm/d/Y H:i:s')
   {
      $date = (!$date) ? date($format) : $date;
      $this->info = DT::getDateTimeArray($date, $format);
      $this->informat = $format;
   }

   public function addDays($day)
   {
      $this->info['day'] += intval($day);
      return $this;
   }

   public function addMonths($month)
   {
      $this->info['month'] += intval($month);
      return $this;
   }

   public function addYears($year)
   {
      $this->info['year'] += intval($year);
      return $this;
   }

   public function addHours($hour)
   {
      $this->info['hour'] += intval($hour);
      return $this;
   }

   public function addMinutes($minute)
   {
      $this->info['minute'] += intval($minute);
      return $this;
   }

   public function addSeconds($second)
   {
      $this->info['second'] += intval($second);
      return $this;
   }

   public function getDate($out = null)
   {
      if (!$out) $out = $this->informat;
      return date($out, mktime($this->info['hour'], $this->info['minute'], $this->info['second'], $this->info['month'], $this->info['day'], $this->info['year']));
   }

   public static function isDate($date, $format)
   {
      return $date == DT::format($date, $format, $format);
   }

   private static function getPattern($format, $n)
   {
      $t = array('GH', 'i', 's', 'FMmn', 'dj', 'Yy');
      $format = preg_replace('@[' . $t[$n] . ']@', '(.*)', $format);
      unset($t[$n]);
      foreach ($t as $v) $format = preg_replace('@[' . $v . ']@', '.*', $format);
      return '@' . $format . '@';
   }

   public static function getHour($date, $format)
   {
      preg_match(self::getPattern($format, 0), $date, $arr);
      if(isset($arr[1])){
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getMinute($date, $format)
   {
      preg_match(DT::getPattern($format, 1), $date, $arr);
      if(isset($arr[1])){
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getSecond($date, $format)
   {
      preg_match(DT::getPattern($format, 2), $date, $arr);
   	  if(isset($arr[1])){
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getDay($date, $format)
   {
      preg_match(DT::getPattern($format, 4), $date, $arr);
   	  if(isset($arr[1])){
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getMonth($date, $format)
   {
      preg_match(DT::getPattern($format, 3), $date, $arr);
      if(isset($arr[1])){
      	if ($res = array_search($arr[1], self::$months['short'])) return intval($res + 1);
      	if ($res = array_search($arr[1], self::$months['full'])) return intval($res + 1);
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getYear($date, $format)
   {
      preg_match(DT::getPattern($format, 5), $date, $arr);
      if(isset($arr[1])){
      	return intval($arr[1]);
      } else {
      	return null;
      }
   }

   public static function getDateTimeArray($date, $format)
   {
      $a = array();
      $a['hour'] = DT::getHour($date, $format);
      $a['minute'] = DT::getMinute($date, $format);
      $a['second'] = DT::getSecond($date, $format);
      $a['month'] = DT::getMonth($date, $format);
      $a['day'] = DT::getDay($date, $format);
      $a['year'] = DT::getYear($date, $format);
      return $a;
   }

   public static function timestamp($date, $format)
   {
      $d = DT::getDateTimeArray($date, $format);
      return mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']);
   }

   public static function format($date, $in, $out)
   {
      return date($out, DT::timestamp($date, $in));
   }

   public static function getDays($year, $month)
   {
      $days = 28;
      if ($month == 2)
      {
         if (($year % 4) == 0 && ($year % 100) != 0 || ($year % 400) == 0) $days++;
      }
      else if ($month < 8) $days = 30 + $month % 2;
      else $days = 31 - $month % 2;
      return $days;
   }

   public static function compare($date1, $date2, $format)
   {
      $v1 = DT::timestamp($date1, $format);
      $v2 = DT::timestamp($date2, $format);
      if ($v1 > $v2) return 1;
      if ($v2 > $v1) return -1;
      return 0;
   }

   public static function difference($date1, $date2, $format, $component = 'day')
   {
      $v1 = DT::timestamp($date1, $format);
      $v2 = DT::timestamp($date2, $format);
      $x = abs($v2 - $v1);
      
      switch ($component)
      {
         default:
         case 'second': return $x;
         case 'minute': return $x / 60;
         case 'hour': return $x / 3600;
         case 'day': return $x / 86400;
         case 'week': return $x / 604800;
         case 'month': return $x / 2592000;
         case 'year': return $x / 31536000;
      }
   }
}

?>