<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\JWX\Exception;

use Throwable;

class UnsupportedJWK extends RuntimeException
{
    public function __construct($message = 'Unsupported JWK', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
