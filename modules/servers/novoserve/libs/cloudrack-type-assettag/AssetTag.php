<?php

namespace NovoServe\Cloudrack\Types;

class AssetTag
{
    /**
     * Asset Tag
     * @var string
     */
    private $assetTag;

    /**
     * ISO-3166 Country Codes
     * @link https://en.wikipedia.org/wiki/ISO_3166
     * @var string[]
     */
    private $countryCodes = ['NL', 'DE', 'US', 'DEV'];

    /**
     * Validates the asset tag and throws an error if incorrect.
     *
     * @param string $assetTag The asset tag to validate.
     * @throws InvalidAssetTagException | InvalidAssetTagLocationException
     */
    public function __construct(string $assetTag = '')
    {
        if (!preg_match('/^((([a-zA-Z]{2,3})-\d{3}-\d{3})|(\d{3}-\d{3}))$/', $assetTag, $pregMatch)) {
            throw new InvalidAssetTagException('Invalid asset tag.');
        }
        if (!empty($pregMatch[3]) && !in_array($pregMatch[3], $this->countryCodes)) {
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
}

class ServerTag extends AssetTag {}
class InvalidAssetTagException extends \Exception {}
class InvalidAssetTagLocationException extends \Exception {}
