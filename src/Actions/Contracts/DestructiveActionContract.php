<?php

namespace Shemi\Laradmin\Actions\Contracts;

use Illuminate\Http\Request;

interface DestructiveActionContract
{

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return string
     */
    public function getOkButtonText();

    /**
     * @return string
     */
    public function getCancelButtonText();

}