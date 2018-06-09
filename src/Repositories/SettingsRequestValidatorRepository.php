<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Exceptions\ValidationException;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\SettingsPage;
use \Shemi\Laradmin\Contracts\Repositories\SettingsRequestValidatorRepository as SettingsRequestValidatorRepositoryContract;

class SettingsRequestValidatorRepository implements SettingsRequestValidatorRepositoryContract
{

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var SettingsPage $page
     */
    protected $page;

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * Validate the given request with the given rules.
     *
     * @param Request $request
     * @param SettingsPage $page
     * @param bool $throw
     * @return \Illuminate\Contracts\Validation\Validator
     * @throws ValidationException
     */
    public function validate(Request $request, SettingsPage $page, $throw = true)
    {
        $this->request = $request;
        $this->page = $page;
        $this->setFields($page->fields);

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
     * @param Collection $fields
     */
    protected function setFields(Collection $fields)
    {
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
        return trim($role);
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

}