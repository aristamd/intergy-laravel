<?php namespace Intergy\Exceptions;

class IntergyLogonError extends \Exception
{
    /**
     * Custom excepetion message when Health Language returns an error
     * @param String $msg Authentication message
     */
    public function __construct( $msg )
    {
        $message = "Your session has expired. {$msg}";
        parent::__construct( $message );
    }
}