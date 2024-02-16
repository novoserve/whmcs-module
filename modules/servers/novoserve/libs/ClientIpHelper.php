<?php declare(strict_types=1);

namespace NovoServe\Whmcs\ResellerModule;

/**
 * @link https://github.com/akrabat/ip-address-middleware Original source of the below code, modified for our situation.
 */
class ClientIpHelper
{
    private const HEADERS_TO_INSPECT = [
        'Forwarded',
        'X-Forwarded-For',
        'X-Forwarded',
        'X-Cluster-Client-Ip',
        'Client-Ip',
    ];

    /**
     * Parses the client's remote IP address from the request headers.
     *
     * @throws \Exception The IP address of the client could not be determined.
     */
    public static function getClientIpAddress(): string
    {
        $ipAddress = null;

        if (isset($_SERVER['REMOTE_ADDR']) && self::isValidIpAddress($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        $requestHeaders = self::getRequestHeaders();
        foreach (self::HEADERS_TO_INSPECT as $header) {
            if (isset($requestHeaders[$header])) {
                $ip = self::getFirstIpAddressFromHeader($header, $requestHeaders[$header]);
                if (self::isValidIpAddress($ip)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        }

        if (empty($ipAddress)) {
            throw new \Exception('Could not determine client IP address');
        }

        return $ipAddress;
    }

    /**
     * Checks whether the given IP address is a valid, non-private/non-reserved IP address.
     */
    private static function isValidIpAddress(string $ip): bool
    {
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return (filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false);
    }

    /**
     * Returns the first IP address found in the given header.
     */
    private static function getFirstIpAddressFromHeader(string $header, string $value): string
    {
        $items = explode(',', $value);
        $headerValue = trim(reset($items));

        if (ucfirst($header) == 'Forwarded') {
            foreach (explode(';', $headerValue) as $headerPart) {
                if (strtolower(substr($headerPart, 0, 4)) == 'for=') {
                    $for = explode(']', $headerPart);
                    $headerValue = trim(substr(reset($for), 4), " \t\n\r\0\x0B" . "\"[]");
                    break;
                }
            }
        }

        return $headerValue;
    }

    /**
     * Gets the request headers from the $_SERVER array (e.g. HTTP_X_FORWARDED_FOR) in the 'standard' format (e.g.
     * X-Forwarded-For)
     *
     * @return string[]
     */
    private static function getRequestHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $header => $value) {
            if (substr($header, 0, 5) !== 'HTTP_') {
                // Not an HTTP header, skip it
                continue;
            }

            $header = ucwords(str_replace(['_', ' '], '-', strtolower($header)));
            $headers[$header] = $value;
        }

        return $headers;
    }
}
