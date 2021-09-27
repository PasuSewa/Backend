<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    /**
     * Note: All of these methods will only return public data.
     * 
     * The decripted credentials are obtained from AuthController, in "grant_access" function
     */

    public function index(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- return all credentials for the user
         */
    }

    public function create(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- validate request must have the following:
         * 
         * 2- create the credential (don't forget to encrypt the data)
         */
    }

    public function update(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- validate request must have the following:
         * 
         * 2- make use of multiple "if" statements to check if credential has set the correct values for each property
         * in case of !isset($credential_property) it must be deleted from db
         * 
         * 3- save all changes (don't forget to encrypt the data)
         */
    }

    public function delete(Request $request, $credential_id)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- delete the credential (this should also delete all other properties related on the db)
         */
    }

    public function find(Request $request, $credential_id)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- find the credential if exists
         */
    }

    public function get_recently_seen(Request $request)
    {
        $user = $request->user();

        /**
         * to do:
         * 
         * 1- get all credentials with "recently_seen" property equal to "true", and sort them by "last_seen" propertty
         */
    }
}
