<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\JWX\Exception;

use Throwable;

class UnsupportedSignatureAlgorithm extends RuntimeException
{
    public function __construct($message = 'Unsupported signature algorithm', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
