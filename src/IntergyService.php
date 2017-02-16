<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) Luca Degasperi <packages@lucadegasperi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Intergy;

use Intergy/Storage/AbstractStorage;

/**
 * This is the authorizer class.
 *
 * @author Luca Degasperi <packages@lucadegasperi.com>
 */
class IntergyService
{
    /**
     * The redirect uri generator.
     *
     * @var bool|null
     */
    protected $patientStorage = null;

    /**
     * Create a new Authorizer instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $issuer
     * @param \League\OAuth2\Server\ResourceServer $checker
     */
    public function __construct()
    {
        //$this->patientStorage = new AbstractStorage();
    }

    public function test(){
        return test;
    }
}
