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
    const APP_REDUNDANT_USER = 5010;
    const APP_NO_USER = 5020;
    const APP_NO_FORM = 5030;
    const statusText = [
      self::APP_REDUNDANT_USER=>"Redundant contact",
      self::APP_NO_USER =>"Contact does not exist",
      self::APP_NO_FORM =>"No form found for ID"
    ];
    public $priorId;

}