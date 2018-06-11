<?php

namespace Shemi\Laradmin\Transformers\Response;

abstract class Transformer
{

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

}