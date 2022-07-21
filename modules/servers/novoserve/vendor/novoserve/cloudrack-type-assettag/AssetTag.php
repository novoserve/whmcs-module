<?php

namespace NovoServe\Cloudrack\Types;

class AssetTag
{
    /** @var string */
    private $assetTag;

    /** @var string */
    private static $regex = '/^((?<PREFIX>[A-Z]{2,3})-)?\d{3}-\d{3}$/';

    /**
     * ISO-3166 Country Codes
     * @link https://en.wikipedia.org/wiki/ISO_3166
     * @var string[]
     */
    private static $countryCodes = ['NL', 'DE', 'US', 'DEV'];

    public static function isValid($tag): bool
    {
        return self::hasValidFormat($tag) && self::hasValidLocation($tag);
    }

    /**
     * Validates the asset tag and throws an error if incorrect.
     *
     * @param string|null $assetTag The asset tag to validate.
     *
     * @throws InvalidAssetTagException | InvalidAssetTagLocationException
     */
    public function __construct(string $assetTag = null)
    {
        if (!self::hasValidFormat($assetTag)) {
            throw new InvalidAssetTagException('Invalid asset tag.');
        }
        if (!self::hasValidLocation($assetTag)) {
            throw new InvalidAssetTagLocationException('Invalid asset tag location.');
        }
        $this->assetTag = $assetTag;
    }

    /**
     * Returns the actual string containing the (validated) asset tag.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->assetTag;
    }

    public function getPrefix(): string
    {
        $prefix = '';

        $pieces = explode('-', $this->assetTag);

        if (count($pieces) === 3) {
            $prefix = $pieces[0];
        }

        return $prefix;
    }

    public function getWithoutPrefix()
    {
        $tag = $this->assetTag;

        $prefix = $this->getPrefix();
        if ($prefix !== '') {
            $prefixLength = strlen($prefix);
            // length has `+1` to include the delimiter
            $tag = substr($tag, $prefixLength + 1);
        }

        return $tag;
    }
        
    /**
     * Matches a given value against the current Tag object
     *
     * @param mixed $tag The value to check against the current Tag object
     *
     * @return bool True if the given value matches the current Tag object, False otherwise
     */
    public function matches($tag): bool
    {
        if (is_scalar($tag)) {
            return $tag === $this->assetTag;
        }

        if (is_object($tag) && method_exists($tag, '__toString')) {
            return (string) $tag === $this->assetTag;
        }

        // Arrays, Resources, Null, and classes without `__toString` method
        return false;
    }

    private static function hasValidFormat($tag): bool
    {
        return self::match($tag) !== [];
    }

    private static function hasValidLocation($tag): bool
    {
        $match = self::match($tag);

        return empty($match['PREFIX'])
            || in_array($match['PREFIX'], self::$countryCodes, true);
    }

    private static function match($tag): array
    {
        $matches = [];

        try {
            preg_match(self::$regex, $tag, $matches);
        } catch (\Throwable $exception) {
        }

        return $matches;
    }
}

class ServerTag extends AssetTag
{
}

abstract class AssetTagException extends \Exception
{
}

class InvalidAssetTagException extends AssetTagException
{
}

class InvalidAssetTagLocationException extends AssetTagException
{
}
