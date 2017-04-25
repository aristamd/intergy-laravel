<?php

namespace Intergy;

use Intergy\Storage\AbstractStorage;
use Intergy\Exceptions\MissingParameterException;

/**
 * Class IntergyService
 * This class contain the storage elements that will allow you access to the Intergy's API.
 */
class IntergyService
{
    /**
     * Object that contains Intergy's configuration.
     *
     * @var Array|null
     */
    protected $config = null;

    /**
     * Repository to call Intergy's Patient info.
     *
     * @var Storage|null
     */
    protected $patientStorage = null;

    /**
     * Repository to call Intergy's Notification info.
     *
     * @var Storage|null
     */
    protected $notificationStorage = null;

    /**
     * Create a new Service Instance.
     *
     * @param   Array    $config    Array with the Intergy's configuration
     * @return  void
     */
    public function __construct( $config )
    {
        // Store the configuration
        $this->config = $config;
    }

    /**
     * Function to set a Patient Storage for the service.
     *
     * @param   Object  $patientStorage     Repository to access Patient's information from Intergy.
     * @return  void
     */
    public function setPatientStorage( $patientStorage )
    {
        $this->patientStorage = $patientStorage;
    }

    /**
     * Function to get a Patient Storage for the service.
     *
     * @return   Object     Repository to access Patient's information from Intergy.
     */
    public function getPatientStorage()
    {
        return $this->patientStorage;
    }

    /**
     * Function to set a Notification Storage for the service.
     *
     * @param   Object  $patientStorage     Repository to access Notification's information from Intergy.
     * @return  void
     */
    public function setNotificationStorage( $notificationStorage )
    {
        $this->notificationStorage = $notificationStorage;
    }

    /**
     * Function to get a Notification Storage for the service.
     *
     * @return   Object     Repository to access Notification's information from Intergy.
     */
    public function getNotificationStorage()
    {
        return $this->notificationStorage;
    }

    /**
     * Search for a patient using the patientId into a specific practice
     *
     * @param   Integer     $providerId     Id of the user(provider) we want to notify.
     * @param   String      $subject        Text we want to see in the subject of the notification
     * @param   String      $subject        Text we will send as the notification's body
     * @return  Integer                     TaskId returned by Intergy's Api
     */
    public function sendNotification( $providerId, $subject, $body)
    {
        // Get the repository to do querys for Intergy's Patients
        $notificationStorage = $this->getNotificationStorage();

        if( empty($notificationStorage) )
        {
            throw new MissingParameterException( __FUNCTION__ );
        }

        // Use the repository to get the patient's information
        return $notificationStorage->sendNotification( $providerId, $subject, $body );
    }


}
