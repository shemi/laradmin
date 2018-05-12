<?php

namespace Shemi\Laradmin\Traits;

use Spatie\Permission\Traits\HasRoles;

trait LaradminUser
{
    use HasRoles;

    public function getNameAttribute($value)
    {
        if($value) {
            return $value;
        }

        $firstName = $this->getAttribute('first_name');
        $lastName = $this->getAttribute('first_name');

        if($firstName || $lastName) {
            return trim("{$firstName} {$lastName}");
        }

        $email = $this->getAttribute('email');

        if($email && $name = array_first(explode('@', $email))) {
            return $name;
        }

        return $this->getKeyName() . ": " . $this->getKey();
    }

}