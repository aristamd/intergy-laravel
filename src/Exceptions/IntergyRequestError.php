<?php namespace Intergy\Exceptions;

class IntergyRequestError extends \Exception
{
    /**
     * Custom excepetion message when Health Language returns an error
     * @param String $msg Authentication message
     */
    public function __construct( $msg )
    {
        $message = "We have found an error while requesting Intergy Api. {$msg}";
        parent::__construct( $message );
    }
}