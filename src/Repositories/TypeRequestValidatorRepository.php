<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\Repositories\TypeRequestValidatorRepository as TypeRequestValidatorRepositoryContract;
use Shemi\Laradmin\Exceptions\ValidationException;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class TypeRequestValidatorRepository implements TypeRequestValidatorRepositoryContract
{

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Type $type
     */
    protected $type;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * Validate the given request with the given rules.
     *
     * @param Request $request
     * @param Type $type
     * @param Model $model
     * @param Collection|null $fields
     * @param bool $throw
     * @return \Illuminate\Contracts\Validation\Validator
     * @throws ValidationException
     */
    public function validate(Request $request, Type $type,
                             Model $model, Collection $fields = null,
                             $throw = true)
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = $model;
        $this->setFields($fields);

        $oldLocal = app()->getLocale();

        app()->setLocale('en');

        $validator = $this->make();

        app()->setLocale($oldLocal);

        if($validator->fails() && $throw) {
            throw new ValidationException($validator);
        }

        return $validator;
    }

    protected function make()
    {
        return $this->getValidationFactory()
            ->make(
                $this->request->all(),
                $this->getRoles(),
                $this->getMessages(),
                $this->getCustomAttributes()
            );
    }

    /**
     * @param Collection|null $fields
     */
    protected function setFields(Collection $fields = null)
    {
        if(! $fields) {
            $fields = $this->model->exists
                ? $this->type->edit_fields
                : $this->type->create_fields;
        }

        $this->fields = $fields->reject(function(Field $field) {
            return $field->read_only || empty($field->getValidationRoles());
        });
    }

    protected function getRoles()
    {
        $roles = [];

        $this->fields->each(function(Field $field) use (&$roles) {
            $roles = array_merge(
                $roles,
                $this->getFieldRoles($field)
            );
        });

        return $roles;
    }

    protected function getFieldRoles(Field $field)
    {
        $newRolesBag = [];
        $rolesBag = $field->getValidationRoles();

        foreach ($rolesBag as $key => $roles) {
            $newRolesBag[$key] = $this->transformFieldRoles($field, $roles);
        }

        return $newRolesBag;
    }

    protected function transformFieldRoles(Field $field, array $roles)
    {
        return array_map(function($role) use ($field) {
            return $this->transformRole($role, $field);
        }, $roles);
    }

    protected function transformRole($role, Field $field)
    {
        $role = trim($role);

        if(! $this->model->exists) {
            return $role;
        }

        if($field->is_password && $role === 'required') {
            $role = 'nullable';
        }

        $modelTable = $this->model->getTable();
        $uniquePattern = "/(unique\:{$modelTable}),?([^,]+)?,?([^,]+)?,?([^,]+)?/";

        if(preg_match($uniquePattern, $role, $uniqueMatches)) {
            $column = $uniqueMatches[2] ?? $field->key;
            $idColumn = $uniqueMatches[4] ?? $this->model->getKeyName();
            $except = $this->model->getAttribute($idColumn);

            $role = "unique:{$modelTable},{$column},{$except},{$idColumn}";
        }

        return $role;
    }

    protected function getMessages()
    {
        return [];
    }

    protected function getCustomAttributes()
    {
        return [];
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }

    public function fresh()
    {
        return new static;
    }

}