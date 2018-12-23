<?php

namespace Shemi\Laradmin\Actions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SplFileInfo;

class ActionResponse implements Responsable
{
    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_REDIRECT = 'redirect';

    protected $message;

    protected $type;

    protected $statusCode;

    protected $filePath;

    protected $redirectTo;

    public function __construct($message, $type, $statusCode = null)
    {

        $this->message = $message;
        $this->type = $type;
        $this->statusCode = $statusCode;
    }

    protected function getMessage(Request $request)
    {
        return "Success!";
    }

    protected function getStatusCode(Request $request)
    {
        if($this->statusCode) {
            return $this->statusCode;
        }

        switch ($this->type) {
            case static::TYPE_ERROR:
                return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return Response::HTTP_OK;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        $download = false;

        if($this->type === self::TYPE_DOWNLOAD) {
            $download = [
                'url' => $this->filePath,
                'name' => $this->getMessage($request)
            ];
        }

        $data = [
            'data' => [
                'message' => $this->getMessage($request),
                'type' => $this->type,
                'redirect' => $this->type === self::TYPE_REDIRECT ? $this->redirectTo : false,
                'download' => $download
            ],
            'resultCode' => $this->type,
            'code'    => $this->getStatusCode($request)
        ];

        return response()->json($data, $this->getStatusCode($request));
    }
}