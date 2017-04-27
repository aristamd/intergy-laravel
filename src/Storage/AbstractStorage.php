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
 * In this repository you will find base functionality that will allow you to do request to Intergy's API.
 */
abstract class AbstractStorage
{
    const AUTHENTICATE_USER = "AuthenticateUser";
    const PRACTICE_LIST = "AuthorizedPracticeListGet";
    const PRACTICE_LOGON = "LogonUserToPractice";
    const PATIENT_SEARCH = "PatientSearch";
    const PATIENT_SUMMARY = "PatientSummaryGet";
    const NOTIFICATION_TASK_CREATE = "NotificationTaskCreate";
    const USER_LIST_GET = "UserListGet";
    const LOGOFF = "LogoffUser";
    const GROUPS_SEARCH = "/groups";
    const INTERGY_CACHE_LIFE = 200;
    const INTERGY_CACHE_SESSION_KEY = "intergy_session_id";
    const INTERGY_CACHE_USER_ID_KEY = "intergy_user_id";
    const INTERGY_CACHE_PRACTICE_ID_KEY = "intergy_practice_id";
    const INTERGY_LOGON_ERROR_CODE = "10";
    const INTERGY_MAX_RETRY = 3;
    
    protected $baseUrl = null;
    protected $token = null;
    protected $username = null;
    protected $password = null;
    protected $applicationName = null;
    protected $licenseId = null;
    protected $userLogon = null;
    protected $sessionId = null;
    protected $userId = null;
    protected $practiceIds = null;
    protected $auditTrailID = null;

    /**
     *  Function that initializes the info required by the request to Intergy's API
     * @return void
     */
    public function __construct( $config )
    {
        // We load the configurations to connect to Health Language
        $this->setConfig( $config );
        // We initializate the session with Intergy
        $this->initSession();
    }

    /**
     *  Function that initializes the info required by the request to Intergy's API
     * @return void
     */
    public function setConfig( $config )
    {
        // We load the configurations to connect to Health Language
        $this->baseUrl = $config[ 'INTERGY_URL' ];
        $this->token = $config[ 'INTERGY_TOKEN' ];
        $this->username = $config[ 'INTERGY_USERNAME' ];
        $this->password = $config[ 'INTERGY_PASSWORD' ];
        $this->applicationName = $config[ 'INTERGY_APPLICATION_NAME' ];
        $this->licenseId = $config[ 'INTERGY_LICENSE_ID' ];
        $this->userLogon = $config[ 'INTERGY_USER_LOGON' ];
    }

    /**
     * Creates urls to different Intergy's endpoints
     *
     * @param   String  $pathName   Name of the route we want to access on Intergy
     * @return  String              Url to the Intergy's endpoint
     */
    protected function getUrl( $pathName )
    {
        $path = null;
        // Select the endpoint path based on name
        switch ( $pathName )
        {
            case 'authenticate-user':
                $path = self::AUTHENTICATE_USER;
                break;
            case 'practice-list':
                $path = self::PRACTICE_LIST;
                break;
            case 'practice-logon':
                $path = self::PRACTICE_LOGON;
                break;
            case 'patient-search':
                $path = self::PATIENT_SEARCH;
                break;
            case 'patient-summary':
                $path = self::PATIENT_SUMMARY;
                break;
            case 'notification-task-create':
                $path = self::NOTIFICATION_TASK_CREATE;
                break;
            case 'user-list-get':
                $path = self::USER_LIST_GET;
                break;
            case 'logoff':
                $path = self::LOGOFF;
                break;
            default:
                $path = self::AUTHENTICATE_USER;
                break;
        }
        // Compose the url for the selected path
        return $this->baseUrl . '/' . $path . "?api_key=" . $this->token;
    }

    /**
     * Checks the api response to identify it there is a logon error
     *
     * @param   Object  $response   Response Object returned by Intergy API
     * @return                      Could return an Error in case session is invalid.
     */
    protected function checkLogonError( $response )
    {
        // We check if it is a logon error
        if( !empty($response) && !empty($response->ErrorCode) && $response->ErrorCode === INTERGY_LOGON_ERROR_CODE )
        {
            // Fires the corresponding exception
            throw new IntergyLogonError( $response->ErrorMessage );
        }
    }

    
    /**
     * Does a http request to the Intergy's API using the parameters provided and returns the response.
     *
     * @param   String  $method         Html method used to do the call(POST,GET,DELETE,PUT,PATCH)
     * @param   String  $url            Url of the Intergy's endpoint we want to access
     * @param   Object  $requestData    Request object that contains required headers and data
     * @return                          Return an object that contains the endpoint response.
     */
    protected function callAPI( $method, $url, $requestData, $retryIntent=0 )
    {
        try
        {
            // Creates a restful client
            $client = new \GuzzleHttp\Client();

            // Do the request
            $apiRequest = $client->request(
                $method,
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $requestData
                ]
            );

            // Returns the response
            $response = json_decode( $apiRequest->getBody()->getContents() );

            // Check if there is an logon error
            $this->checkLogonError( $response );

            // Returns the API response
            return $response;
        }
        // In case there is a logon error we retry the request after login
        catch ( IntergyLogonError $logonError )
        {
            // We try to do the call a defined amount of times
            if( $retryIntent < self::INTERGY_MAX_RETRY )
            {
                // Creates a new session
                $this->initSession( true );
                // Retry the request
                return $this->callAPI( $method, $url, $requestData, $retryIntent + 1 );
            }
            else
            {
                // We log the error in Kibana
                Log::error(
                    'Intergy: We can not create a new session to Intergy',
                    [
                        'uri' => $url,
                        "query" => $requestData,
                        'message' => $logonError->getMessage()
                    ]
                );
                // We have reach the max amount of intents then we fire the error.
                throw new IntergyRequestError( $logonError->getMessage() );
            }
        }
        catch ( RequestException $re )
        {
            // We log the error in Kibana
            Log::error(
                'Intergy: Call to API has failed',
                [
                    'uri' => $url,
                    "query" => $requestData,
                    'message' => $re->getMessage()
                ]
            );
            throw new IntergyRequestError( $re->getMessage() );
        }
    }

    /**
     *  Calls specific uri from the Health Language API to do a request
     * @param   String   $userName          Username use to login
     * @param   String   $userPassword      Password of the user
     * @param   String   $licenseId         Lisence required by Intergy
     * @param   String   $sessionId         Session identifier for the user
     * @param   String   $userLogon         Indentifier used to logon
     * @param   String   $applicationName   Name of the application
     * @return  Object                      Data returned from API ('data' key)
     */
    private function authenticateUser( $userName, $userPassword, $licenseId, $sessionId, $userLogon, $applicationName )
    {
        $uri = null;
        $queryData = null;

        try
        {
            // Get the endpoint URL
            $uri = $this->getUrl( 'authenticate-user' );
            // Create the request
            $queryData = [
                'UserName' => $userName,
                'UserPassword' => $userPassword,
                'Credential' => [
                    "LicenseID" => $licenseId,
                    "SessionID" => $sessionId,
                    "UserLogon" => $userLogon,
                    "ApplicationName" => $applicationName,
                    "UserMachineName" => "",
                ]
            ];

            // Send the request to the endpoint
            $response = $this->callAPI( 'POST', $uri,$queryData );

            return $response;
        }
        catch ( RequestException $re )
        {
            // Log errors
            Log::error(
                'Intergy: Call to authenticate user failed',
                [
                    'uri' => $uri,
                    "query" => $queryData,
                    'message' => $re->getMessage()
                ]
            );
            throw new IntergyRequestError( $re->getMessage() );
        }
    }

    /**
     *  Calls specific uri from the Health Language API to do a request
     * @param   String   $userName          Username use to login
     * @param   String   $userPassword      Password of the user
     * @param   String   $licenseId         Lisence required by Intergy
     * @param   String   $sessionId         Session identifier for the user
     * @param   String   $userLogon         Indentifier used to logon
     * @param   String   $applicationName   Name of the application
     * @return  Object                      Data returned from API ('data' key)
     */
    public function logoff()
    {
        $uri = null;
        $queryData = null;

        try
        {
            // Get the endpoint URL
            $uri = $this->getUrl( 'logoff' );
            // Create the request
            $queryData = [
                'UserName' => $this->username,
                'UserPassword' => $this->password,
                'Credential' => [
                    "LicenseID" => $this->licenseId,
                    "SessionID" => $this->sessionId,
                    "UserLogon" => $this->userLogon,
                    "ApplicationName" => $this->applicationName,
                    "UserMachineName" => "",
                ],
                'AuditTrailID'=> $this->auditTrailID,
            ];

            // Send the request to the endpoint
            $response = $this->callAPI( 'POST', $uri,$queryData );

            Log::info("User has been logoff successfully from Intergy");

            return $response;
        }
        catch ( RequestException $re )
        {
            // Log errors
            Log::error(
                'Intergy: Call to logoff user failed',
                [
                    'uri' => $uri,
                    "query" => $queryData,
                    'message' => $re->getMessage()
                ]
            );
            throw new IntergyRequestError( $re->getMessage() );
        }
    }

    /**
     * Gets a list of practices for the user
     *
     * @return  Array   List of Practices
     */
    private function listPractices()
    {
        $uri = null;
        $queryData = null;

        try
        {
            // Get the endpoint URL
            $uri = $this->getUrl( 'practice-list' );
            // Creates the request
            $queryData = [
                'UserID' => $this->userId,
                'Credential' => [
                    "LicenseID" => $this->licenseId,
                    "SessionID" => $this->sessionId,
                    "UserLogon" => $this->userLogon,
                    "ApplicationName" => $this->applicationName,
                    "UserMachineName" => "",
                ]
            ];

            // Calls to the endpoint
            $response = $this->callAPI( 'POST', $uri, $queryData );

            return $response;
        }
        catch ( RequestException $re )
        {
            // Logs the error
            Log::error(
                'Intergy: Call to authenticate user failed',
                [
                    'uri' => $uri,
                    "query" => $queryData,
                    'message' => $re->getMessage()
                ]
            );
            throw new IntergyRequestError( $re->getMessage() );
        }
    }

    /**
     * Links the session with a specific Practice
     *
     * @param   Integer     $practiceId   Id of the practice you want to access
     * @return  void
     */
    protected function logonToPractice( $practiceId, $userId, $licenseId, $sessionId, $userLogon, $applicationName )
    {
        // Get the endpoint url
        $uri = $this->getUrl( 'practice-logon' );
        // Creates the request
        $queryData = [
            'UserID' => $this->userId,
            'PracticeID' => $practiceId,
            'Credential' => [
                "LicenseID" => $this->licenseId,
                "SessionID" => $this->sessionId,
                "UserLogon" => $this->userLogon,
                "ApplicationName" => $this->applicationName,
                "UserMachineName" => "",
            ]
        ];

        // Send the request to the API
        $this->callAPI( 'POST', $uri, $queryData );
    }

    protected function getDefaultPractice()
    {
        if( empty($this->practiceIds) || sizeof($this->practiceIds) == 0 )
        {
            throw new IntergyRequestError( "There are not registered practices for the user" );
        }

        return $this->practiceIds[0];
    }

    /**
     * Extract the session id from endpoint's response
     *
     * @param   Object  $response   Response received from server
     * @return  String              Session Id assigned to the user
     */
    private function getSessionId( $response )
    {
        // Check the parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // Checks it there is a session error
        if( !empty($response->ErrorCode) )
        {
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        // Gets the session Id
        return $response->SessionID;
    }

    /**
     * Extract the session id from endpoint's response
     *
     * @param   Object  $response   Response received from server
     * @return  String              Session Id assigned to the user
     */
    private function getAuditTrailId( $response )
    {
        // Check the parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // Checks it there is a session error
        if( !empty($response->ErrorCode) )
        {
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        // Gets the session Id
        return $response->AuditTrailID;
    }

    /**
     * Extract the session id from endpoint's response
     *
     * @param   Object  $response   Response received from server
     * @return  String              Session Id assigned to the user
     */
    private function getUserId( $response )
    {
        // Check the parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        if( !empty($response->ErrorCode) )
        {
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        return $response->UserID;
    }

    /**
     * Extract the practice id from the Practice object
     * This function will be used by a Mapping function
     *
     * @param   Object     $practice    Practice object returned by the server
     * @return  Integer                 Practice identificator
     */
    private function extractPracticeId( $practice )
    {
        // Checks parameters
        if( empty($practice) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        return $practice->PracticeID;
    }

    /**
     * Extract a list of Practice Ids from API response
     *
     * @param   Object  $respose    Response received from server that contains the list of practices
     * @return  Array               Array of practices Ids
     */
    private function getPracticeIds( $response )
    {
        // Check the parameters
        if( empty($response) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // Checks if there is an error
        if( !empty($response->ErrorCode) )
        {
            throw new IntergyRequestError( $response->ErrorMessage );
        }

        // Get the list of practices
        $practices = $response->UserPracticeList;

        // Extract the id of every practice
        $ids = array_map( [$this,'extractPracticeId'], $practices );

        // Return of practice ids
        return $ids;
    }

    /**
     * Initialize a Intergy Session required to do queries
     *
     * @param   Boolean     $forceToRefresh     Value that idicates if we need to force to create a new session
     * @return  void
     */
    protected function initSession( $forceToRefresh=false )
    {
        // Checks if we have this query result on cache and return it
        /*if( !$forceToRefresh &&
            Cache::has( self::INTERGY_CACHE_SESSION_KEY ) &&
            Cache::has( self::INTERGY_CACHE_USER_ID_KEY ) &&
            Cache::has( self::INTERGY_CACHE_PRACTICE_ID_KEY )
        )
        {
            $this->sessionId = Cache::get( self::INTERGY_CACHE_SESSION_KEY );
            $this->userId = Cache::get( self::INTERGY_CACHE_USER_ID_KEY );
            $this->practiceIds = Cache::get( self::INTERGY_CACHE_PRACTICE_ID_KEY );
            return;
        }*/

        // Autheticate the user
        $response = $this->authenticateUser(
            $this->username,
            $this->password,
            $this->licenseId,
            $this->sessionId,
            $this->userLogon,
            $this->applicationName
        );

        // Get the session Id
        $this->sessionId = $this->getSessionId( $response );
        // Get the audit trail id
        $this->auditTrailID = $this->getAuditTrailId( $response );
        // Saves the result into cache
        //Cache::put( self::INTERGY_CACHE_SESSION_KEY, $this->sessionId, self::INTERGY_CACHE_LIFE );
        // Get the user Id
        $this->userId = $this->getUserId( $response );
        // Saves the result into cache
        //Cache::put( self::INTERGY_CACHE_USER_ID_KEY, $this->userId, self::INTERGY_CACHE_LIFE );

        // Get the list of practices
        $response = $this->listPractices();
        $this->practiceIds = $this->getPracticeIds( $response );

        // Saves the result into cache
        //Cache::put( self::INTERGY_CACHE_PRACTICE_ID_KEY, $this->practiceIds, self::INTERGY_CACHE_LIFE );
    }
}
