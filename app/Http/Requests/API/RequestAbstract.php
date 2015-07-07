<?php
namespace CC\Http\Requests\API;

use CC\Http\Requests\Request;
use Illuminate\Http\JsonResponse;

/**
 * Basic validating request for the api.
 * @author b3nl <code@b3nl.de>
 * @category Requests
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
abstract class RequestAbstract extends Request
{
    /**
     * Get the proper failed validation response for the request.
     * @param  array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        $parentResponse = parent::response($errors);

        return $errors ? new JsonResponse($errors, 400) : $parentResponse;
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Returns the json response for every api request.
     * @return bool
     */
    public function wantsJson()
    {
        return true;
    } // function
}
