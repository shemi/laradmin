<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\Models\Type;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $status_code = 200;

    protected $slug = null;

    public function __construct()
    {
        view()->share('user', $this->user());
    }

    protected function user()
    {
        return $this->guard()->user();
    }

    protected function guard()
    {
        return Auth::guard(config('laradmin.guard'));
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getSlug(Request $request)
    {
        if ($this->slug) {
            return $this->slug;
        }

        return explode('.', $request->route()->getName())[1];
    }

    /**
     * @param Request|string $request
     * @return Type
     */
    protected function getTypeBySlug($request)
    {
        $typeSlug = $request instanceof Request ?
            $this->getSlug($request) :
            $request;

        $type = Type::where('slug', $typeSlug)->first();

        if(! $type) {
            throw new DataNotFoundException($typeSlug);
        }

        return $type;
    }

    /**
     * @return int
     */
    protected function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param int $status_code
     * @return $this
     */
    protected function setStatusCode($status_code)
    {
        $this->status_code = $status_code;

        return $this;
    }


    protected function responseNotFound($message = 'Not found.')
    {
        return $this->setStatusCode(404)->responseWithError($message);
    }

    protected function responseUnauthorized(Request $request, $message = 'Unauthorized.')
    {
        if(! $request->wantsJson()) {
            return response()
                ->view('laradmin::errors.403', [], 403);
        }

        return $this->setStatusCode(403)->responseWithError($message);
    }

    protected function responseNotAuthorized($message = 'not Authorized.')
    {
        return $this->setStatusCode(401)->responseWithError($message);
    }

    protected function responseValidationError($messages = 'Check all your fields.')
    {
        if(is_string($messages)) {
            $messages = [
                'form' => [$messages]
            ];
        }

        return response()->json($messages, 422);
    }

    protected function responseInternalError($message = 'Internal error.')
    {
        return $this->setStatusCode(500)->responseWithError($message);
    }

    protected function responseBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)
            ->responseWithError($message, 'Bad Request');
    }

    protected function responseWithError($message, $resultCode = 'err')
    {
        return response()->json([
            'message' => $message,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ], $this->getStatusCode());
    }



    protected function response($data, $headers = [], $resultCode = 'ok')
    {
        $data = [
            'data' => $data,
            'resultCode' => $resultCode,
            'code'    => $this->getStatusCode()
        ];

        return response()->json($data, $this->getStatusCode(), $headers);
    }
}