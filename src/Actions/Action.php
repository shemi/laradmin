<?php

namespace Shemi\Laradmin\Actions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shemi\Laradmin\Actions\Contracts\DestructiveActionContract;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Repositories\QueryRepository;

abstract class Action {

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * @param Collection $models
     * @param Type $type
     * @param Request $request
     *
     * @return Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    abstract public function apply(Collection $models, Type $type, Request $request);

    public function run(Type $type, Request $request)
    {
        $models = QueryRepository::asCollection($request, $type);

        try {
            $res = $this->apply($models, $type, $request);
        } catch (\Exception $exception) {
            return static::error($exception);
        }

        return $res;
    }

    /**
     * @param Request $request
     * @return array
     */
    abstract public function fields(Request $request);

    public function canRun($user)
    {
        return true;
    }
    
    /**
     * @return string
     */
    public function getLabel()
    {
        if($this->label) {
            return $this->label;
        }

        $this->label = Str::title(Str::snake($this->getName(), ' '));

        return $this->label;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if($this->name) {
            return $this->name;
        }

        $this->name = class_basename(static::class);

        return $this->name;
    }

    public function getFields(Request $request = null)
    {
        $fields = $this->fields($request ?: app('request'));

        return is_array($fields) ? $fields : [];
    }

    public function isDestructive()
    {
        return $this instanceof DestructiveActionContract;
    }

    public static function message($message)
    {
        return ActionSuccessResponse::make($message);
    }

    public static function error($message)
    {
        return ActionErrorResponse::make($message);
    }

    public static function warning($message)
    {
        return ActionWarningResponse::make($message);
    }

    public static function redirect($url, $message = null)
    {
        return ActionRedirectResponse::make($url, $message);
    }

    public static function download($file, $name = null)
    {
        return ActionDownloadResponse::make($file, $name);
    }

    public function toArray(Request $request = null)
    {
        $destructive = false;

        if($this->isDestructive()) {
            $destructive = [
                'message' => $this->getMessage(),
                'ok' => $this->getOkButtonText(),
                'cancel' => $this->getCancelButtonText()
            ];
        }

        return [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'fields' => $this->getFields($request),
            'destructive' => $destructive
        ];
    }

}