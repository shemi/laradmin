<?php

namespace Shemi\Laradmin\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Shemi\Laradmin\Models\Media;
use Shemi\Laradmin\Models\SettingsPage;
use Shemi\Laradmin\Models\Type;
use Spatie\Image\Image;

class UploadsController extends Controller
{

    public function upload($typeSlug, Request $request)
    {
        if(starts_with($typeSlug, "settings__")) {
            $modelClass = SettingsPage::class;
            $typeSlug = str_replace("settings__", "", $typeSlug);
        } else {
            $modelClass = Type::class;
        }

        $model = $modelClass::where('slug', $typeSlug)->first();
        $key = $request->input('field_form_key');
        $sessionId = $request->session()->getId();
        $path = "laradmin_temp/{$sessionId}/";

        $roles = [
            'field_form_key' => [
                "required"
            ],
            'file' => [
                'required',
                'file'
            ]
        ];

        if($model) {
            $field = $modelClass::extractAllFields(collect($model->fields))
                ->first(function($field) use ($key) {
                    return $field->key === $key;
                });

            if(! $field) {
                abort(402);
            }

            $roles['file'] = array_merge($roles['file'], $field->validation);
        }

        $path = $path."{$key}";

        $this->validate($request, $roles);

        $file = $request->file('file');

        $fileName = $file->hashName();

        $fileInfo = [
            'md5_name' => $fileName,
            'name' => $file->getClientOriginalName(),
            'ext' => $file->extension(),
            'size' => $file->getSize(),
            'alt' => '',
            'caption' => ''
        ];

        if(! $tempPath = $file->storeAs($path, $fileName)) {
            abort(500);
        }

        $fileInfo['temp_path'] = $tempPath;

        $fileInfo['uri'] = route('laradmin.serve', [
            'mediaId' => 'temp-folder',
            'fileName' => $fileName,
            'temp-path' => $tempPath
        ]);

        return $this->response($fileInfo);
    }

    public function serve($mediaId, $fileName, Request $request)
    {
        if($mediaId === 'temp-folder') {
            return $this->serveTempFile($fileName, $request);
        }

        return $this->serveMediaFile($mediaId, $fileName, $request);
    }

    protected function serveTempFile($fileName, Request $request)
    {
        if(! $path = $request->input('temp-path')) {
            abort(404);
        }

        $path = storage_path('app/'.$path);

        if(! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    protected function serveMediaFile($mediaId, $fileName, Request $request)
    {
        $media = Media::findOrFail($mediaId);

        $conversion = $request->input("pc", '');

        return response()->file($media->getPath($conversion));
    }

    public function delete($typeSlug, Request $request)
    {

    }

    protected function deleteTempFile()
    {

    }

    protected function deleteModelMediaFile()
    {

    }



}