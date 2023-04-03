<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\JWX;

class DecodeOptions
{
    /**
     * @var array
     */
    protected $allowedAlgorithms;

    /**
     * All algorithms without NONE
     */
    const SECURE_ALGORITHMS = [
        'HS256',
        'HS384',
        'HS512',
        //
        'RS256',
        'RS384',
        'RS512',
//        //
//        'ES256',
//        'ES384',
//        'ES512',
    ];

    /**
     * @param array $allowedAlgorithms
     */
    public function __construct(array $allowedAlgorithms = self::SECURE_ALGORITHMS)
    {
        $this->allowedAlgorithms = $allowedAlgorithms;
    }

    /**
     * @param string $algorithm
     * @return bool
     */
    public function isAllowedAlgorithms(string $algorithm): bool
    {
        return in_array($algorithm, $this->allowedAlgorithms, true);
    }
}
