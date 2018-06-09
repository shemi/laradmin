<?php
namespace Shemi\Laradmin\Contracts;

use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;

interface FormFieldContract
{
    public function handle(Field $field, Model $type, $data);

    public function createContent(Field $field, Model $type, $data);

    public function getCodename();

    public function getName();

    public function transformRequest(Field $field, $data);

    public function transformResponse(Field $field, $data);

}