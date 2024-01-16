<?php

declare(strict_types=1);

namespace QDM\Traits;

use QDM\DataModelException;

trait SafeJsonTrait
{
    public function jsonDecode(string $json, bool $assoc = false, int $depth = 512, int $options = 0) : mixed
    {
        $decoded = json_decode($json, $assoc, $depth, $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DataModelException(DataModelException::CODE_JSON_SERIALIZE_ERROR, [json_last_error_msg()]);
        }
        return $decoded;
    }

    public function jsonEncode(mixed $value, int $options = 0, int $depth = 512) : string
    {
        $encoded = json_encode($value, $options, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DataModelException(DataModelException::CODE_JSON_SERIALIZE_ERROR, [json_last_error_msg()]);
        }
        return $encoded;
    }

    public function jsonDecodeCatch(
        string $json,
        bool $assoc = false,
        int $depth = 512,
        int $options = 0,
        array &$errors = []
    ) : mixed {
        try {
            return $this->jsonDecode($json, $assoc, $depth, $options);
        } catch (DataModelException $e) {
            $errors[] = $e->getMessage();
            return null;
        }
    }

    public function jsonEncodeCatch(
        mixed $value,
        int $options = 0,
        int $depth = 512,
        array &$errors = []
    ) : ?string {
        try {
            return $this->jsonEncode($value, $options, $depth);
        } catch (DataModelException $e) {
            $errors[] = $e->getMessage();
            return null;
        }
    }
}
