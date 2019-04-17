<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/13/19
 * Time: 12:00 AM
 */

namespace App;


class AppException extends \Exception
{
    const APP_REDUNDANT_USER = 5001;
    const APP_NO_USER = 5002;
    const statusText = [
      self::APP_REDUNDANT_USER=>"Redundant contact",
      self::APP_NO_USER =>"Contact does not exist"
    ];
}