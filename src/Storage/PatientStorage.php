<?php namespace Intergy\Storage;

use GuzzleHttp\Exception\RequestException;
use Log;
use \Exception;
use Cache;
use Intergy\Exceptions\MissingParameterException;
use Intergy\Exceptions\IntergyRequestError;
use Intergy\Exceptions\IntergyLogonError;

/**
 * Class Intergy Repository
 * It is a wrapper for the restful api
 * @package App\Services\Repositories
 *
 * In this repository you will find functionality that will allow you to get information
 * information about a patient into the Intergys API.
 */
class PatientStorage extends AbstractStorage
{
    /**
     * Search for a patient using the patientId into a specific practice
     *
     * @param   Integer     $patientId      Id of the patient we want to find.
     * @param   Integer     $practiceId     Id of the practice we want to search
     * @return  Object                      List of patients that matches with the search
     */
    public function searchPatient( $patientId, $practiceId=null )
    {
        // Get the pactice Id in case we haven't received one
        if( empty($practiceId) )
        {
            $practiceId = $this->getDefaultPractice();
        }

        // We need to be logged into the practice we want to search
        $this->logonToPractice( $practiceId, $this->userId, $this->licenseId, $this->sessionId, $this->userLogon, $this->applicationName );
        
        // Get Url to the search patient endpoint
        $uri = $this->getUrl( 'patient-search' );

        // Create a Request
        $queryData = [
            'Credential' => [
                "LicenseID" => $this->licenseId,
                "SessionID" => $this->sessionId,
                "UserLogon" => $this->userLogon,
                "ApplicationName" => $this->applicationName,
                "UserMachineName" => "",
            ],
            "PatientNumber" => $patientId
        ];

        // Send the request to the server and wait for the response
        $response = $this->callAPI( 'POST', $uri, $queryData );
        return $this->getUsers( $response );
    }

    /**
     * Search for a patient using the patientId into a specific practice
     *
     * @param   Integer     $patientId      Id of the patient we want to find.
     * @param   Integer     $practiceId     Id of the practice we want to search
     * @return  Object                      List of patients that matches with the search
     */
    public function getPatient( $patientId, $practiceId=null )
    {
        // Get the pactice Id in case we haven't received one
        if( empty($practiceId) )
        {
            $practiceId = $this->getDefaultPractice();
        }

        // We need to be logged into the practice we want to search
        $this->logonToPractice( $practiceId, $this->userId, $this->licenseId, $this->sessionId, $this->userLogon, $this->applicationName );

        // Get Url to the search patient endpoint
        $uri = $this->getUrl( 'patient-summary' );

        // Create a Request
        $queryData = [
            'Credential' => [
                "LicenseID" => $this->licenseId,
                "SessionID" => $this->sessionId,
                "UserLogon" => $this->userLogon,
                "ApplicationName" => $this->applicationName,
                "UserMachineName" => "",
            ],
            "PatientID" => $patientId
        ];

        // Send the request to the server and wait for the response
        $response = $this->callAPI( 'POST', $uri, $queryData );
        return $this->getUser( $response );
    }

    /**
     * Extract the practice id from the Practice object
     * This function will be used by a Mapping function
     *
     * @param   Object     $response    Response returned by the server when searching for users
     * @return  Array                   List of users
     */
    private function getUser( $response )
    {
        // Checks parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        return $response->PersonDetails;
    }

    /**
     * Extract the practice id from the Practice object
     * This function will be used by a Mapping function
     *
     * @param   Object     $response    Response returned by the server when searching for users
     * @return  Array                   List of users
     */
    private function getUsers( $response )
    {
        // Checks parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        return $response->PatientList;
    }
}