<?php namespace Core\Exceptions;

/**
 * Class IntergyLogonError
 * This exception should be used to manage errors related with missing parameters when calling functions for Intergy.
 */
class IntergyMissingParameterException extends \Exception
{
    /**
     * Custom excepetion message when validating parameters
     * @param String $functionName Where the error happend
     */
    public function __construct( $functionName )
    {
        $message = "There are missing parameters when try to call the function: $functionName.";
        parent::__construct( $message );
    }
}