<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * A domain failure whose message is safe to show to the end user verbatim.
 */
class ResellerHubException extends RuntimeException
{
    /**
     * @param array<string, mixed> $context Extra data for logs. Never rendered to the user.
     */
    public function __construct(
        string          $message,
        protected array $context = [],
        ?Throwable      $previous = null,
    )
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Structured context for logging.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
