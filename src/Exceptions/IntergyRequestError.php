<?php namespace Intergy\Exceptions;

/**
 * Class IntergyRequestError
 * This exception should be used to manage errors related with the requests sent to Intergy's API.
 */
class IntergyRequestError extends \Exception
{
    /**
     * Custom excepetion message when Intergy's API returns an error
     * @param String $msg Authentication message
     */
    public function __construct( $msg )
    {
        $message = "We have found an error while requesting Intergy Api. {$msg}";
        parent::__construct( $message );
    }
}