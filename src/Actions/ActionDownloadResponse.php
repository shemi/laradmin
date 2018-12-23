<?php

namespace Shemi\Laradmin\Actions;


use Illuminate\Http\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActionDownloadResponse extends ActionResponse
{
    protected $filePath;

    public static function make($path, $fileName = null)
    {
        return (new static($fileName, static::TYPE_DOWNLOAD))
            ->setFilePath($path);
    }

    public function setFilePath($path)
    {
        if($path instanceof BinaryFileResponse) {
            if(! $this->message) {
                $str = $path->headers->get('content-disposition');

                preg_match('/.*filename=[\'\"]?([^\"]+)/', $str, $matches);

                $this->message = isset($matches[1]) ? $matches[1] : 'download';
            }

            $path = route('laradmin.actions.download', [
                'path' => $path->getFile()->getPathname(),
                'filename' => $this->message
            ]);
        }

        $this->filePath = $path;

        return $this;
    }

    protected function getMessage(Request $request)
    {
        return $this->message;
    }

}