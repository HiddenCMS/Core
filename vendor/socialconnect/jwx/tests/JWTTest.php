<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
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

class JWTTest extends AbstractTestCase
{
    /**
     * @return array
     */
    protected function getJWKSet()
    {
        return [
            [
                'kid' => 'testSigKey',
                'kty' => 'RS256',
                'n' => 'TEST',
                'e' => 'TEST'
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getTestHeader(string $alg = 'RS256', string $kid = 'testSigKey')
    {
        return [
            'alg' => $alg,
            'kid' => $kid
        ];
    }

    public function testValidateClaimsSuccess()
    {
        $token = new JWT(
            array(
                'nbf' => time(),
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );

        // to skip warning
        parent::assertTrue(true);
    }

    public function testValidateClaimsNbfFail()
    {
        $token = new JWT(
            array(
                'nbf' => $nbf = time() + 10,
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage(sprintf(
            'nbf (Not Fefore) claim is not valid %s',
            date(DateTime::RFC3339, $nbf)
        ));

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateClaimsNbfScrew()
    {
        JWT::$screw = 30;

        $token = new JWT(
            array(
                'nbf' => $nbf = time() + 10,
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );

        JWT::$screw = 0;

        // to skip warning
        parent::assertTrue(true);
    }

    public function testValidateClaimsExpNotNumeric()
    {
        $token = new JWT(
            array(
                'nbf' => time(),
                'iat' => time(),
                'exp' => 'invalid',
            ),
            $this->getTestHeader()
        );

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('exp (Expiration Time) must be numeric');

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateClaimsExpExpired()
    {
        $token = new JWT(
            array(
                'nbf' => time(),
                'iat' => time(),
                'exp' => $exp = time() - 20,
            ),
            $this->getTestHeader()
        );

        parent::expectException(ExpiredJWT::class);
        parent::expectExceptionMessage(
            sprintf(
                'exp (Expiration Time) claim is not valid %s',
                date(DateTime::RFC3339, $exp)
            )
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateHeaderSuccess()
    {
        $token = new JWT(
            [],
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateHeader',
            new JWKSet(['keys' => []]),
            new DecodeOptions(['RS256'])
        );

        // to skip warning
        parent::assertTrue(true);
    }

    public function testValidateHeaderNoAlg()
    {
        $token = new JWT(
            [],
            [
                'kid' => 'testSigKey'
            ]
        );

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('No alg inside header');

        self::callProtectedMethod(
            $token,
            'validateHeader',
            '',
            new DecodeOptions([])
        );
    }

    public function testValidateHeaderNoKid()
    {
        $token = new JWT(
            [],
            [
                'alg' => 'RS256'
            ]
        );

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('No kid inside header');

        self::callProtectedMethod(
            $token,
            'validateHeader',
            new JWKSet(['keys' => []]),
            new DecodeOptions(['RS256'])
        );
    }
    
    public function testValidateHeaderNoKidSingleKey()
    {
        $kset = new JWKSet(['keys' => [
            JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs512.key.pub')->toArray(),
        ]]);
        
        $token = new JWT(
            [],
            [
                'alg' => 'RS256'
            ]
        );

        self::callProtectedMethod(
            $token,
            'validateHeader',
            $kset,
            new DecodeOptions(['RS256'])
        );
        
        parent::assertTrue(true);
    }

    public function testDecodeWrongNumberOfSegments()
    {
        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('Wrong number of segments');

        JWT::decode(
            'lol',
            'heh',
            new DecodeOptions()
        );
    }

    public function getEncodeToDecodeDataProvider()
    {
        $kid = 'super-kid-' . time();

        return [
            // RSA
            [
                file_get_contents(__DIR__ . '/assets/rs256.key'),
                file_get_contents(__DIR__ . '/assets/rs256.key.pub'),
                'RS256',
                [],
                new EncodeOptions(),
                new DecodeOptions(['RS256']),
            ],
            [
                file_get_contents(__DIR__ . '/assets/rs384.key'),
                file_get_contents(__DIR__ . '/assets/rs384.key.pub'),
                'RS384',
                [],
                new EncodeOptions(),
                new DecodeOptions(['RS384']),
            ],
            [
                file_get_contents(__DIR__ . '/assets/rs512.key'),
                file_get_contents(__DIR__ . '/assets/rs512.key.pub'),
                'RS512',
                [],
                new EncodeOptions(),
                new DecodeOptions(['RS512']),
            ],
            // ES
           [
               file_get_contents(__DIR__ . '/assets/es256.pem'),
               file_get_contents(__DIR__ . '/assets/es256.pub'),
               'ES256',
               [],
               new EncodeOptions(),
               new DecodeOptions(['ES256']),
           ],
           [
               file_get_contents(__DIR__ . '/assets/es384.pem'),
               file_get_contents(__DIR__ . '/assets/es384.pub'),
               'ES384',
               [],
               new EncodeOptions(),
               new DecodeOptions(['ES384']),
           ],
           [
               file_get_contents(__DIR__ . '/assets/es512.pem'),
               file_get_contents(__DIR__ . '/assets/es512.pub'),
               'ES512',
               [],
               new EncodeOptions(),
               new DecodeOptions(['ES512']),
           ],
            // JWKSet for RSA
            [
                file_get_contents(__DIR__ . '/assets/rs512.key'),
                new JWKSet([
                    'keys' => [
                        array_merge(
                            JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs512.key.pub')->toArray(),
                            [
                                'kid' => $kid
                            ]
                        )
                    ]
                ]),
                'RS512',
                ['kid' => $kid],
                new EncodeOptions(),
                new DecodeOptions(['RS512']),
            ],
            // HS
            [
                'secret',
                'secret',
                'HS256',
                [],
                new EncodeOptions(),
                new DecodeOptions(['HS256']),
            ],
            [
                'secret',
                'secret',
                'HS384',
                [],
                new EncodeOptions(),
                new DecodeOptions(['HS384']),
            ],
            [
                'secret',
                'secret',
                'HS512',
                [],
                new EncodeOptions(),
                new DecodeOptions(['HS512']),
            ],
        ];
    }

    /**
     * @dataProvider getEncodeToDecodeDataProvider
     * @param string $privateKeyOrSecret
     * @param string|JWKSet $publicKeyOrSecret
     * @param string $alg
     * @param EncodeOptions $encodeOptions
     * @param DecodeOptions $decodeOptions
     */
    public function testEncodeToDecodeSuccess(
        string $privateKeyOrSecret,
        $publicKeyOrSecret,
        string $alg,
        array $header,
        EncodeOptions $encodeOptions,
        DecodeOptions $decodeOptions
    ) {
        $payload = [
            'uid' => '2955b34c-7a3b-4d96-9fd1-2930c18f9989'
        ];

        $token = new JWT($payload, $header);
        $jwtAsString = $token->encode($privateKeyOrSecret, $alg, $encodeOptions);

        $jwt = JWT::decode($jwtAsString, $publicKeyOrSecret, $decodeOptions);

        parent::assertSame($payload, $jwt->getPayload());

        $headers = $jwt->getHeaders();
        parent::assertArrayHasKey('alg', $headers);
    }
}
