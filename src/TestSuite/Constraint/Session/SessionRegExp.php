<?php
declare(strict_types=1);

namespace App\TestSuite\Constraint\Session;

use Cake\Utility\Hash;
use PHPUnit\Framework\Constraint\Constraint;

class SessionRegExp extends Constraint
{
    protected string $path;

    /**
     * Constructor
     *
     * @param string $path Session Path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Compare session value
     *
     * @param mixed $other Value to compare with
     * @return bool
     */
    public function matches($other): bool
    {
        // Server::run calls Session::close at the end of the request.
        // Which means, that we cannot use Session object here to access the session data.
        // Call to Session::read will start new session (and will erase the data).
        $value = Hash::get($_SESSION, $this->path);
        return preg_match($other, $value) > 0;
    }

    /**
     * Assertion message
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf(
            'PCRE pattern matches the value from session path "%s"',
            $this->path
        );
    }
}
