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

namespace Weblizards\TagManagementBundle\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;

class TagConfigDataBinder
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function bind(Config $tag, array $data): Config
    {
        $payload = $this->buildPayload($data);

        return $this->serializer->denormalize($payload, Config::class, null, [
            'object_to_populate' => $tag,
        ]);
    }

    /**
     * Merge canonical payloads and legacy aliases into one serializer-friendly config structure.
     */
    private function buildPayload(array $data): array
    {
        $payload = TagConfigNormalizer::normalizeForModel($data, false);

        $payload['items'] = $this->extractItems($data, $payload['items'] ?? null);
        $payload['params'] = $this->extractParams($data, $payload['params'] ?? null);

        return $payload;
    }

    /**
     * Accept structured items and the historical flat `item.<id>.<field>` payload during migration.
     */
    private function extractItems(array $data, mixed $items): array
    {
        if (is_array($items)) {
            return array_values(array_filter(array_map(
                [$this, 'normalizeItem'],
                $items
            )));
        }

        $legacyItems = [];

        foreach ($data as $key => $value) {
            if (!is_string($key) || strpos($key, 'item.') !== 0) {
                continue;
            }

            $cleanKeyParts = explode('.', $key, 3);
            if (count($cleanKeyParts) !== 3) {
                continue;
            }

            [, $itemId, $field] = $cleanKeyParts;
            $legacyItems[$itemId][$field] = $value;
        }

        return array_values(array_filter(array_map(
            [$this, 'normalizeLegacyItem'],
            $legacyItems
        )));
    }

    /**
     * Accept structured params and the historical flat `params.nameN`/`params.valueN` payload.
     */
    private function extractParams(array $data, mixed $params): array
    {
        if (is_array($params)) {
            return array_values(array_filter(array_map(
                [$this, 'normalizeParam'],
                $params
            )));
        }

        $legacyParams = [];

        foreach ($data as $key => $value) {
            if (!is_string($key) || strpos($key, 'params.name') !== 0) {
                continue;
            }

            $index = substr($key, strlen('params.name'));
            $legacyParams[] = [
                'name' => $value,
                'value' => $data['params.value' . $index] ?? '',
            ];
        }

        return array_values(array_filter(array_map(
            [$this, 'normalizeParam'],
            $legacyParams
        )));
    }

    /**
     * Normalize one incoming item to the canonical runtime shape expected by the Config model.
     */
    private function normalizeItem(mixed $item): ?array
    {
        if (!is_array($item)) {
            return null;
        }

        $normalized = TagConfigNormalizer::normalizeItemForModel($item);
        $normalized['date'] = $this->normalizeExpiryDate($normalized['date'] ?? null);

        return $normalized;
    }

    /**
     * Upgrade one legacy flat item payload, including the old split date/time expiry fields.
     */
    private function normalizeLegacyItem(array $item): ?array
    {
        $normalizedItem = $this->normalizeItem($item);
        if ($normalizedItem === null) {
            return null;
        }

        if (!empty($item['date'])) {
            // Legacy requests used separate date/time fields. When time is omitted we keep the item
            // active until the end of the selected day instead of expiring it at midnight.
            $normalizedItem['date'] = $this->combineLegacyDateAndTime(
                (string) $item['date'],
                (string) ($item['time'] ?? '')
            );
        }

        return $normalizedItem;
    }

    /**
     * Filter empty params and normalize accepted aliases into the canonical pair shape.
     */
    private function normalizeParam(mixed $param): ?array
    {
        if (!is_array($param)) {
            return null;
        }

        $normalizedParams = TagConfigNormalizer::normalizeParamsForModel([$param]);
        if ($normalizedParams === []) {
            return null;
        }

        return $normalizedParams[0];
    }

    /**
     * Keep empty values nullable while preserving already-normalized ISO strings as-is.
     */
    private function normalizeExpiryDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return (string) $value;
    }

    /**
     * Combine historical split date/time fields into one ISO-8601 timestamp.
     */
    private function combineLegacyDateAndTime(string $date, string $time): ?string
    {
        $date = trim($date);
        $time = trim($time);

        if ($date === '') {
            return null;
        }

        $datePart = explode('T', $date)[0];
        $timePart = $time === '' ? '23:59:59' : explode('T', $time)[1] ?? $time;
        $timestamp = strtotime($datePart . 'T' . $timePart);

        if ($timestamp === false) {
            return null;
        }

        return gmdate('c', $timestamp);
    }
}
