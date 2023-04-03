<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\JWX;

class EncodeOptions
{
    /**
     * @var int|null
     */
    protected $expirationTime = null;

    /**
     * @param int $expirationTime
     */
    public function setExpirationTime(int $expirationTime)
    {
        $this->expirationTime = $expirationTime;
    }

    /**
     * @return int|null
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * @return bool
     */
    public function hasExpirationTime(): bool
    {
        return $this->expirationTime !== null;
    }
}