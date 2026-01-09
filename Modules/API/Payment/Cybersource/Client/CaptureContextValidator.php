<?php

namespace Modules\API\Payment\Cybersource\Client;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

readonly class CaptureContextValidator
{
    public function __construct(
        private CybersourceClient $client,
    ) {
    }

    /**
     * Fully validates the capture context JWT:
     *  - checks structure (3 segments)
     *  - extracts "kid" from header
     *  - fetches public JWK from /flex/v2/public-keys/{kid}
     *  - verifies JWT signature using RS256
     *  - optionally checks "exp" claim is not in the past
     */
    public function validate(string $jwt): bool
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                Log::warning('Cybersource capture context has invalid JWT format.');
                return false;
            }

            [$headerB64, $payloadB64] = $parts;

            $headerJson = $this->base64UrlDecode($headerB64);
            $header     = json_decode($headerJson, true);

            if (!is_array($header) || empty($header['kid']) || empty($header['alg'])) {
                Log::warning('Cybersource capture context header is missing kid or alg.');
                return false;
            }

            if (strtoupper((string) $header['alg']) !== 'RS256') {
                Log::warning('Cybersource capture context uses unsupported algorithm.', [
                    'alg' => $header['alg'],
                ]);
                return false;
            }

            $kid = (string) $header['kid'];

            // Fetch public JWK for this kid.
            $jwk = $this->client->fetchPublicKeyForKid($kid);
            if (!$jwk) {
                Log::warning('Cybersource JWK not found for kid.', ['kid' => $kid]);
                return false;
            }

            // Wrap as JWKS as expected by JWK::parseKeySet().
            $jwks = ['keys' => [$jwk]];
            $keys = JWK::parseKeySet($jwks);

            if (empty($keys)) {
                Log::warning('Cybersource JWKS parsed to empty key set.');
                return false;
            }

            // Cryptographically verifies signature (throws on invalid signature/alg).
            JWT::decode($jwt, $keys);

            // Optional: check exp if present.
            $exp = $this->getExp($jwt);
            if ($exp !== null && $exp < time()) {
                Log::warning('Cybersource capture context is expired.', [
                    'exp' => $exp,
                    'now' => time(),
                ]);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::warning('Cybersource capture context validation failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Validates transient token integrity using the public key embedded in the capture context payload (flx.jwk).
     * This ensures:
     *  - token was issued by Cybersource
     *  - token was not tampered with in transit
     *
     * IMPORTANT: captureContextJwt must be validated with validate() before trusting its payload.
     */
    public function validateTransientToken(string $transientTokenJwt, string $captureContextJwt): bool
    {
        try {
            // Parse capture context payload and extract embedded JWK.
            $capturePayload = $this->getPayload($captureContextJwt);
            $jwk = $capturePayload['flx']['jwk'] ?? null;

            if (!is_array($jwk) || empty($jwk['kty']) || empty($jwk['n']) || empty($jwk['e'])) {
                Log::warning('Capture context does not contain a valid embedded JWK (flx.jwk).');
                return false;
            }

            // Firebase\JWT may require "alg" in the key set. Force RS256.
            $jwk['alg'] = $jwk['alg'] ?? 'RS256';

            $jwks = ['keys' => [$jwk]];
            $keys = JWK::parseKeySet($jwks);

            if (empty($keys)) {
                Log::warning('Embedded capture context JWK parsed to empty key set.');
                return false;
            }

            // Verify transient token signature (throws on invalid signature/alg).
            JWT::decode($transientTokenJwt, $keys);

            // Ensure token is not expired (if exp present).
            $exp = $this->getExp($transientTokenJwt);
            if ($exp !== null && $exp < time()) {
                Log::warning('Cybersource transient token is expired.', [
                    'exp' => $exp,
                    'now' => time(),
                ]);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::warning('Cybersource transient token validation failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Returns JWT exp claim or null if missing/invalid.
     */
    public function getExp(string $jwt): ?int
    {
        $payload = $this->getPayload($jwt);
        $exp = $payload['exp'] ?? null;

        return is_numeric($exp) ? (int) $exp : null;
    }

    /**
     * Decodes JWT payload JSON into array (no signature verification here).
     * Use validate()/validateTransientToken() for cryptographic verification.
     */
    private function getPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return [];
        }

        $payloadJson = $this->base64UrlDecode($parts[1]);
        $payload = json_decode($payloadJson, true);

        return is_array($payload) ? $payload : [];
    }

    /**
     * Decodes a base64url encoded string into raw binary string.
     */
    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $data = strtr($data, '-_', '+/');

        return (string) base64_decode($data);
    }
}
