<?php

namespace Shemi\Laradmin\Http\Controllers\Api;

use Shemi\Laradmin\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    protected $status_code = 200;
    
    public function base()
    {
        return $this->response([
            'version' => 'dev',
            'user_status' => \Auth::check()
        ]);
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