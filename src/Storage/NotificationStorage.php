<?php namespace Intergy\Storage;

use GuzzleHttp\Exception\RequestException;
use Log;
use \Exception;
use Cache;
use Intergy\Exceptions\IntergyMissingParameterException;
use Intergy\Exceptions\IntergyRequestError;
use Intergy\Exceptions\IntergyLogonError;

/**
 * Class Intergy Repository
 * It is a wrapper for the restful api
 * @package App\Services\Repositories
 *
 * In this repository you will find functionality that will allow you to send notifications
 * using the Intergys API.
 */
class NotificationStorage extends AbstractStorage
{
    /**
     * Search for a patient using the patientId into a specific practice
     *
     * @param   Integer     $providerId     Id of the user(provider) we want to notify.
     * @param   String      $subject        Text we want to see in the subject of the notification
     * @param   String      $subject        Text we will send as the notification's body
     * @return  Integer                     TaskId returned by Intergy's Api
     */
    public function sendNotification( $userId, $subject, $body, $practiceId=null )
    {
        // Get the pactice Id in case we haven't received one
        if( empty($practiceId) )
        {
            $practiceId = $this->getDefaultPractice();
        }

        // We check that all the parameters has been provided
        if( empty($userId) || empty($subject) || empty($body) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // We need to be logged into the practice we want to search
        $this->logonToPractice( $practiceId, $this->userId, $this->licenseId, $this->sessionId, $this->userLogon, $this->applicationName );
        
        // Get Url to the search patient endpoint
        $uri = $this->getUrl( 'notification-task-create' );

        // Create a Request
        $queryData = [
            'Credential' => [
                "LicenseID" => $this->licenseId,
                "SessionID" => $this->sessionId,
                "UserLogon" => $this->userLogon,
                "ApplicationName" => $this->applicationName,
                "UserMachineName" => "",
            ],
            "ToUserID" => $userId,
            "TaskType" => "CLUPT",
            "Subject" => $subject,
            "Body" => $body
        ];

        // Send the request to the server and wait for the response
        $response = $this->callAPI( 'POST', $uri, $queryData );
        return $this->getNotificationResult( $response );
    }

    /**
     * Search for a patient using the patientId into a specific practice
     *
     * @param   Integer     $providerId     Id of the user(provider) we want to notify.
     * @param   String      $subject        Text we want to see in the subject of the notification
     * @param   String      $subject        Text we will send as the notification's body
     * @return  Integer                     TaskId returned by Intergy's Api
     */
    public function getTaskTypeList( $practiceId=null )
    {
        // Get the pactice Id in case we haven't received one
        if( empty($practiceId) )
        {
            $practiceId = $this->getDefaultPractice();
        }

        // We need to be logged into the practice we want to search
        $this->logonToPractice( $practiceId, $this->userId, $this->licenseId, $this->sessionId, $this->userLogon, $this->applicationName );
        
        // Get Url to the search patient endpoint
        $uri = $this->getUrl( 'task-type-list' );

        // Create a Request
        $queryData = [
            'Credential' => [
                "LicenseID" => $this->licenseId,
                "SessionID" => $this->sessionId,
                "UserLogon" => $this->userLogon,
                "ApplicationName" => $this->applicationName,
                "UserMachineName" => "",
            ]
        ];

        // Send the request to the server and wait for the response
        $response = $this->callAPI( 'POST', $uri, $queryData );
        return $this->getTaskTypesFromResult( $response );
    }

    /**
     * Extract the TaskID from the Notification Create Task request
     *
     * @param   Object     $response    Response returned by the server when send notification to Intergy
     * @return  Int                     Task Id
     */
    private function getNotificationResult( $response )
    {
        // Checks parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // We check if it is a logon error
        if( !empty($response) && !empty($response->ErrorCode) )
        {
            // Fires the corresponding exception
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        return $response->TaskID;
    }

    /**
     * Extract the TaskID from the Notification Create Task request
     *
     * @param   Object     $response    Response returned by the server when send notification to Intergy
     * @return  Int                     Task Id
     */
    private function getTaskTypesFromResult( $response )
    {
        // Checks parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // We check if it is a logon error
        if( !empty($response) && !empty($response->ErrorCode) )
        {
            // Fires the corresponding exception
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        return $response->TaskTypeList;
    }
}