JWX
===

[![Packagist](https://img.shields.io/packagist/v/socialconnect/jwx.svg?style=flat-square)](https://packagist.org/packages/socialconnect/jwx)
[![License](http://img.shields.io/packagist/l/SocialConnect/jwx.svg?style=flat-square)](https://github.com/SocialConnect/jwx/blob/master/LICENSE)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FSocialConnect%2Fjwx.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FSocialConnect%2Fjwx?ref=badge_shield)

## [Documentation](https://socialconnect.lowl.io/jwx.html) :: [Getting Started](https://socialconnect.lowl.io/jwx.html)

Implementation:

- JWT (JSON Web Token) [RFC 7519](https://tools.ietf.org/html/rfc7519)
- JWK (JSON Web Keys) [RFC 7517](https://tools.ietf.org/html/rfc7517)
- JWKs (JSON Web Key Set) [RFC 7517](https://tools.ietf.org/html/rfc7517#section-8.4)

## Encode

```php
<?php

$jwt = new \SocialConnect\JWX\JWT([
    'uid' => 5,
]);

$encodeOptions = new \SocialConnect\JWX\EncodeOptions();
$encodeOptions->setExpirationTime(600);

$token = $jwt->encode('TEST', 'HS256', $encodeOptions);
var_dump($token);
```

## Decode

```php
<?php

$decodeOptions = new \SocialConnect\JWX\DecodeOptions(['HS256']);
$token = \SocialConnect\JWX\JWT::decode('TEST', $token, $decodeOptions);

var_dump($token);
```

### License

This project is open-sourced software licensed under the MIT License.

See the [LICENSE](LICENSE) file for more information.


[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FSocialConnect%2Fjwx.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FSocialConnect%2Fjwx?ref=badge_large)