<?php
/**
 * SocialConnect project
 */

namespace Test\JWX;

use DateTime;
use SocialConnect\JWX\DecodeOptions;
use SocialConnect\JWX\EncodeOptions;
use SocialConnect\JWX\Exception\ExpiredJWT;
use SocialConnect\JWX\Exception\InvalidJWT;
use SocialConnect\JWX\JWK;
use SocialConnect\JWX\JWKSet;
use SocialConnect\JWX\JWT;

class JWKSetTest extends AbstractTestCase {
    
    public function testDefaultKeyNoKeys() {
        $set = new JWKSet(['keys' => []]);
        
        parent::assertFalse($set->hasDefaultKey());
        
        parent::expectException("RuntimeException");
        $set->getDefaultKey();
    }
    
    public function testDefaultKeyMultipleKeys() {
        $set = new JWKSet(['keys' => [
            JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs256.key.pub')->toArray(),
            JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs384.key.pub')->toArray(),
            
        ]]);
        
        parent::assertFalse($set->hasDefaultKey());
        
        parent::expectException("RuntimeException");
        $set->getDefaultKey();
    }
    
    public function testDefaultKeySingle() {
        $set = new JWKSet(['keys' => [
            JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs256.key.pub')->toArray(),
            
        ]]);
        
        parent::assertTrue($set->hasDefaultKey());
        
        parent::assertInstanceOf(JWK::class, $set->getDefaultKey());
    }
}