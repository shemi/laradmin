<?php

namespace Shemi\Laradmin\Transformers\Response;

abstract class Transformer
{

    /**
     * @var array $only
     */
    protected $only;

    /**
     * @return MediaTransformer
     */
    protected function getMediaTransformer()
    {
        return new MediaTransformer();
    }

    /**
     * @return RelationshipTransformer
     */
    protected function getRelationshipTransformer()
    {
        return new RelationshipTransformer();
    }

    /**
     * @return JsonTransformer
     */
    protected function getJsonTransformer()
    {
        return new JsonTransformer();
    }

    /**
     * @return ModelTransformer
     */
    protected function getModelValueTransformer()
    {
        return new ModelTransformer();
    }

    /**
     * @param array $keys
     * @return $this
     */
    public function only($keys)
    {
        if(! isset($this->only)) {
            $this->only = [];
        }

        $this->only = array_merge($this->only, (array) $keys);

        return $this;
    }

}