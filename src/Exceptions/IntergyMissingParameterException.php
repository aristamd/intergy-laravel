<?php namespace Core\Exceptions;

class IntergyMissingParameterException extends \Exception
{
    /**
     * Custom excepetion message when Health Language returns an error
     * @param String $msg Authentication message
     */
    public function __construct( $functionName )
    {
        $message = "There are missing parameters when try to call the function: $functionName.";
        parent::__construct( $message );
    }
}