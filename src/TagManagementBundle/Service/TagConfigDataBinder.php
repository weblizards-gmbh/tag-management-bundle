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
            'items',
            'params',
        ];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = $data[$key];
            }
        }

        if (array_key_exists('items', $payload) && !is_array($payload['items'])) {
            $payload['items'] = [];
        }
        if (array_key_exists('params', $payload) && !is_array($payload['params'])) {
            $payload['params'] = [];
        }

        return $payload;
    }

}
