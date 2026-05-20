<?php
/**
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) Weblizards GmbH (https://www.weblizards.de)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace Weblizards\TagManagementBundle\Model\Tag;

final class TagConfigNormalizer
{
    private const STORAGE_FIELD_MAP = [
        'siteId' => 'site_id',
        'urlPattern' => 'url_pattern',
        'textPattern' => 'text_pattern',
        'httpMethod' => 'http_method',
        'creationDate' => 'creation_date',
        'modificationDate' => 'modification_date',
        'enabledInEditmode' => 'enabled_in_editmode',
        'temporarily_disabled' => 'disabled',
    ];

    /**
     * Accept legacy and canonical field names and map them onto the in-memory Config model shape.
     */
    public static function normalizeForModel(array $data, bool $includeMeta = true): array
    {
        $normalized = [
            'name' => self::getFirstDefinedValue($data, ['name', 'id'], ''),
            'description' => self::getFirstDefinedValue($data, ['description'], ''),
            'disabled' => self::normalizeBoolean(self::getFirstDefinedValue($data, ['disabled', 'temporarily_disabled'], false)),
            'siteId' => self::normalizeNullableString(self::getFirstDefinedValue($data, ['siteId', 'site_id'], null)),
            'urlPattern' => self::normalizeString(self::getFirstDefinedValue($data, ['urlPattern', 'url_pattern'], '')),
            'textPattern' => self::normalizeString(self::getFirstDefinedValue($data, ['textPattern', 'text_pattern'], '')),
            'httpMethod' => self::normalizeString(self::getFirstDefinedValue($data, ['httpMethod', 'http_method'], '')),
            'items' => self::normalizeItemsForModel(self::getFirstDefinedValue($data, ['items'], [])),
            'params' => self::normalizeParamsForModel(self::getFirstDefinedValue($data, ['params'], [])),
        ];

        if ($includeMeta) {
            $normalized['creationDate'] = self::normalizeNullableInt(self::getFirstDefinedValue($data, ['creationDate', 'creation_date'], null));
            $normalized['modificationDate'] = self::normalizeNullableInt(self::getFirstDefinedValue($data, ['modificationDate', 'modification_date'], null));
        }

        return $normalized;
    }

    /**
     * Convert the model shape into the persisted snake_case storage format.
     */
    public static function normalizeForStorage(array $data): array
    {
        $modelData = self::normalizeForModel($data);

        return [
            'name' => $modelData['name'],
            'description' => $modelData['description'],
            'disabled' => $modelData['disabled'],
            'site_id' => $modelData['siteId'],
            'url_pattern' => $modelData['urlPattern'],
            'text_pattern' => $modelData['textPattern'],
            'http_method' => $modelData['httpMethod'],
            'items' => self::normalizeItemsForStorage($modelData['items']),
            'params' => self::normalizeParamsForStorage($modelData['params']),
            'creation_date' => $modelData['creationDate'],
            'modification_date' => $modelData['modificationDate'],
        ];
    }

    /**
     * Normalize filter/order field names to the persisted storage keys.
     */
    public static function normalizeFieldNameForStorage(string $fieldName): string
    {
        return self::STORAGE_FIELD_MAP[$fieldName] ?? $fieldName;
    }

    /**
     * Normalize one item into the canonical runtime shape used by services and the admin UI.
     */
    public static function normalizeItemForModel(array $item): array
    {
        return [
            'code' => self::normalizeCode(self::getFirstDefinedValue($item, ['code'], '')),
            'element' => self::normalizeString(self::getFirstDefinedValue($item, ['element'], '')),
            'position' => self::normalizeString(self::getFirstDefinedValue($item, ['position'], '')),
            'disabled' => self::normalizeBoolean(self::getFirstDefinedValue($item, ['disabled', 'temporarily_disabled'], false)),
            'enabledInEditmode' => self::normalizeBoolean(self::getFirstDefinedValue($item, ['enabledInEditmode', 'enabled_in_editmode'], false)),
            'date' => self::normalizeNullableString(self::getFirstDefinedValue($item, ['date'], null)),
        ];
    }

    /**
     * Persist item keys in snake_case while keeping their semantic values intact.
     */
    public static function normalizeItemForStorage(array $item): array
    {
        $normalized = self::normalizeItemForModel($item);

        return [
            'code' => $normalized['code'],
            'element' => $normalized['element'],
            'position' => $normalized['position'],
            'disabled' => $normalized['disabled'],
            'enabled_in_editmode' => $normalized['enabledInEditmode'],
            'date' => $normalized['date'],
        ];
    }

    /**
     * @param mixed $items
     *
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeItemsForModel($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $normalizedItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalizedItems[] = self::normalizeItemForModel($item);
        }

        return $normalizedItems;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeItemsForStorage(array $items): array
    {
        return array_map([self::class, 'normalizeItemForStorage'], $items);
    }

    /**
     * @param mixed $params
     *
     * @return array<int, array{name:string, value:string}>
     */
    public static function normalizeParamsForModel($params): array
    {
        if (!is_array($params)) {
            return [];
        }

        $normalizedParams = [];

        foreach ($params as $param) {
            if (!is_array($param)) {
                continue;
            }

            $name = trim((string) ($param['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $normalizedParams[] = [
                'name' => $name,
                'value' => self::normalizeString($param['value'] ?? ''),
            ];
        }

        return $normalizedParams;
    }

    /**
     * @param array<int, array{name:string, value:string}> $params
     *
     * @return array<int, array{name:string, value:string}>
     */
    public static function normalizeParamsForStorage(array $params): array
    {
        return self::normalizeParamsForModel($params);
    }

    /**
     * Return the first matching alias so legacy and canonical payloads can coexist during migration.
     */
    private static function getFirstDefinedValue(array $data, array $keys, mixed $default): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return $default;
    }

    private static function normalizeString(mixed $value): string
    {
        return trim((string) $value);
    }

    /**
     * Preserve snippet code verbatim so formatting-sensitive user input survives normalization.
     */
    private static function normalizeCode(mixed $value): string
    {
        return (string) $value;
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Normalize booleans from mixed legacy input such as "1", "true", or checkbox payloads.
     */
    private static function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        return !empty($value);
    }
}
