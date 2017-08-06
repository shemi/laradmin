<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Shemi\Laradmin\Data\DataNotFoundException;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Type;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $status_code = 200;

    protected $slug = null;

    public function __construct()
    {
        $user = null;

        if(Auth::check()) {
            $user = Laradmin::model('User')->find(Auth::id());
        }

        view()->share('user', $user);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getSlug(Request $request)
    {
        if ($this->slug) {
            return $this->slug;
        }

        return explode('.', $request->route()->getName())[1];
    }

    /**
     * @param Request $request
     * @return Type
     */
    public function getTypeBySlug(Request $request)
    {
        $typeSlug = $this->getSlug($request);

        $type = Type::where('slug', $typeSlug)->first();

        if(! $type) {
            throw new DataNotFoundException($typeSlug);
        }

        return $type;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param int $status_code
     * @return $this
     */
    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;

        return $this;
    }


    public function responseNotFound($message = 'Not found.')
    {
        return $this->setStatusCode(404)->responseWithError($message);
    }



    public function responseNotAuthorized($message = 'not Authorized.')
    {
        return $this->setStatusCode(401)->responseWithError($message);
    }

    public function responseValidationError($messages = 'Check all your fields.')
    {
        if(is_string($messages)) {
            $messages = [
                'form' => [$messages]
            ];
        }

        return response()->json($messages, 422);
    }

    public function responseInternalError($message = 'Internal error.')
    {
        return $this->setStatusCode(500)->responseWithError($message);
    }

    public function responseBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)->responseWithError($message);
    }

    public function responseWithError($message, $resultCode = 'err')
    {

        return response()->json([
            'message' => $message,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ], $this->getStatusCode());
    }



    public function response($data, $headers = [], $resultCode = 'ok')
    {
        $data = [
            'data' => $data,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ];


        return response($data, $this->getStatusCode(), $headers);
    }
}