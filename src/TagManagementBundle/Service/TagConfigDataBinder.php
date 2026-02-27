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

    private function buildPayload(array $data): array
    {
        $payload = [];
        $allowedKeys = [
            'name',
            'description',
            'disabled',
            'siteId',
            'urlPattern',
            'textPattern',
            'httpMethod',
        ];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = $data[$key];
            }
        }

        $payload['items'] = $this->buildItems($data);
        $payload['params'] = $this->buildParams($data);

        return $payload;
    }

    private function buildItems(array $data): array
    {
        $items = [];

        foreach ($data as $key => $value) {
            if (0 !== strpos($key, 'item.')) {
                continue;
            }

            $parts = explode('.', $key, 3);
            if (3 !== count($parts)) {
                continue;
            }

            if ('time' === $parts[2]) {
                continue;
            }

            if ('date' === $parts[2]) {
                $date = $value;
                $value = null;

                $timeKey = $parts[0] . '.' . $parts[1] . '.time';
                if (!empty($date) && !empty($data[$timeKey])) {
                    $time = explode('T', $data[$timeKey]);
                    $date = explode('T', $date);
                    $value = strtotime($date[0] . 'T' . $time[1]);
                }
            }

            $items[$parts[1]][$parts[2]] = $value;
        }

        return array_values($items);
    }

    private function buildParams(array $data): array
    {
        $params = [];
        $index = 0;

        while (isset($data['params.name' . $index]) || isset($data['params.value' . $index])) {
            if (isset($data['params.name' . $index])) {
                $params[] = [
                    'name' => $data['params.name' . $index],
                    'value' => $data['params.value' . $index] ?? '',
                ];
            }
            ++$index;
        }

        return $params;
    }
}
