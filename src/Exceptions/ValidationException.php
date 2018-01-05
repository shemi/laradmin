<?php

namespace Shemi\Laradmin\Exceptions;

use Illuminate\Validation\ValidationException as BaseException;

class ValidationException extends BaseException implements ExceptionContract
{

}