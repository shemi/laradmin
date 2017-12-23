<?php

namespace Shemi\Laradmin\JsonSchema;

use Illuminate\Support\Collection;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as BaseValidator;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;

class Validator
{

    protected $validator;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var object
     */
    protected $data;

    /**
     * @var array
     */
    protected $errors = [];

    public function __construct(Schema $schema, $data)
    {
        $this->schema = $schema;
        $this->data = $data;
        $this->errors = collect([]);

        $this->validator = new BaseValidator;
    }

    /**
     * @param Schema $schema
     * @param $data
     *
     * @throws \Exception
     *
     * @return static
     */
    public static function create(Schema $schema, $data)
    {
        if(is_array($data)) {
            $data = BaseValidator::arrayToObjectRecursive($data);
        }

        if(is_string($data)) {
            $data = (object) json_decode($data);
        }

        if(! is_object($data)) {
            throw new InvalidArgumentException("Data most be array OR valid json string OR object");
        }

        return new static($schema, $data);
    }

    public function validate($prefix = '')
    {
        $this->validator->validate(
            $this->data,
            json_decode($this->schema->toJson()),
            Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_TYPE_CAST
        );

        if(! $this->validator->isValid()) {
            $this->setErrors($this->validator->getErrors(), $prefix);
        }

        return $this;
    }

    protected function setErrors($errors, $prefix)
    {
        foreach ($errors as $error) {
            $this->errors[$prefix.$error['property']] = [$error['message']];
        }
    }

    public function errors()
    {
        return $this->errors;
    }

    public function data()
    {
        return $this->data;
    }

}