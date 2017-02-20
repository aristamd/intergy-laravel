<?php namespace Intergy\Exceptions;

/**
 * Class IntergyLogonError
 * This exception should be used to manage errors related with the Intergy's session.
 */
class IntergyLogonError extends \Exception
{
    /**
     * Custom excepetion message when Intergy's logon returns an error
     * @param String $msg Authentication message
     */
    public function __construct( $msg )
    {
        $message = "Your session has expired. {$msg}";
        parent::__construct( $message );
    }
}