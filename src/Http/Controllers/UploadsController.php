<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Type;

class UploadsController extends Controller
{

    public function upload($typeSlug, Request $request)
    {
        $type = Type::where('slug', $typeSlug)->first();
        $key = $request->input('field_form_key');
        $path = "laradmin_temp/";
        $roles = [
            'field_form_key' => [
                "required"
            ],
            'file' => [
                'required',
                'file'
            ]
        ];

        if($type) {
            $path = $path."types/{$type->id}/";
            $field = $type->fields
                ->first(function($field) use ($key) {
                    return $field->key === $key;
                });

            if(! $field) {
                abort(402);
            }

            $roles['file'] = array_merge($roles['file'], $field->validation);
        } else {
            $path = $path."{$typeSlug}/";
        }

        $path = $path."{$key}/";

        $this->validate($request, $roles);

        $file = $request->file('file');

        $fileInfo = [
            'temp_path' => $path.$file->hashName(),
            'md5_name' => $file->hashName(),
            'name' => $file->getClientOriginalName(),
            'ext' => $file->extension(),
            'size' => $file->getSize()
        ];

        if(! $file->store($path)) {
            abort(500);
        }

        return $this->response($fileInfo);
    }

}