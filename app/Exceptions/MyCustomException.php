<?php

namespace App\Exceptions;

use Exception;

class MyCustomException extends Exception
{
    protected $details;

    public function __construct($message, $code = 0, $details = [], Exception $previous = null)
    {
        $this->details = $details;

        // Appelle le constructeur parent pour conserver le message et le code
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retourne les détails supplémentaires.
     */
    public function getDetails()
    {
        return $this->details;
    }
}
