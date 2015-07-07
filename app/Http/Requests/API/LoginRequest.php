<?php

namespace CC\Http\Requests\API;

use CC\Http\Requests\API\RequestAbstract;
use Illuminate\Http\JsonResponse;

/**
 * Handles the api login of an user.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API\Resource\Update
 * @version $id$
 */
class LoginRequest extends RequestAbstract
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     * @todo Check for Remote Addr and correct credentials, count, ip block. Refactor.
     */
    public function authorize()
    {
        return true;
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return ['email' => 'required|email', 'password' => 'required'];
    } // function

    /**
     * Get the proper failed validation response for the request.
     * @param  array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        $parentResponse = parent::response($errors);

        return $errors ? new JsonResponse($errors, 404) : $parentResponse;
    } // function
}
