<?php

namespace Intergy;

use Intergy\Storage\AbstractStorage;

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
}
