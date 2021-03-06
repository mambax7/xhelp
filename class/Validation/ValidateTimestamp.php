<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 * Class ValidateTimestamp
 */
class ValidateTimestamp extends validation\Validator
{
    /**
     * Private
     * $timestamp the date/time to validate
     */
    public $timestamp;

    //! A constructor.

    /**
     * Constucts a new ValidateTimestamp object subclass or Validator
     * @param int  $timestamp the string to validate
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates a timestamp
     * @return void
     */
    public function validate()
    {
    }
}
