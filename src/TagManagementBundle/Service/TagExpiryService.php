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

use Carbon\Carbon;
use Weblizards\TagManagementBundle\Model\Tag\Config;

class TagExpiryService
{
    /**
     * Disable expired tag items and persist changes.
     *
     * @return array{tags_updated:int, items_disabled:int}
     */
    public function disableExpiredItems(): array
    {
        $list = new Config\Listing();
        $tags = $list->load();

        $tagsUpdated = 0;
        $itemsDisabled = 0;
        $now = time();

        /** @var Config $tag */
        foreach ($tags as $tag) {
            $changed = false;
            $items = $tag->getItems();

            if (!is_array($items) || $items === []) {
                continue;
            }

            foreach ($items as $index => $item) {
                // Expiry is stored as ISO-8601 UTC ("Z") or legacy epoch; interpret accordingly.
                $expiryTimestamp = $this->resolveExpiryTimestamp($item['date'] ?? null);
                if ($expiryTimestamp === null) {
                    continue;
                }

                if (!empty($item['disabled'])) {
                    continue;
                }

                if ($now > $expiryTimestamp) {
                    $items[$index]['disabled'] = true;
                    $itemsDisabled++;
                    $changed = true;
                }
            }

            if ($changed) {
                $tag->setItems($items);
                $tag->save();
                $tagsUpdated++;
            }
        }

        return [
            'tags_updated' => $tagsUpdated,
            'items_disabled' => $itemsDisabled,
        ];
    }

    private function resolveExpiryTimestamp($value): ?int
    {
        if (empty($value) || $value === '0' || $value === 0) {
            return null;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $timestamp = (int) $value;

            return $timestamp > 0 ? $timestamp : null;
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->getTimestamp();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
