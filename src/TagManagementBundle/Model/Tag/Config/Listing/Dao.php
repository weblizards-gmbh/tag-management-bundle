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

namespace Weblizards\TagManagementBundle\Model\Tag\Config\Listing;

use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Model\Tag\Config\Dao as ConfigDao;
use Weblizards\TagManagementBundle\Model\Tag\Config\Listing;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;

/**
 * @property Listing $model
 */
class Dao extends ConfigDao
{
    /**
     * Load matching tag configs and keep filter/order handling compatible with normalized storage keys.
     *
     * @throws \Exception
     */
    public function load(): array
    {
        $properties = [];
        $ids = array_unique(array_merge(
            $this->loadIdList(),
            array_filter(array_map(static fn (array $row): string => (string) ($row['id'] ?? ''), $this->getLegacyRows()))
        ));

        foreach ($ids as $id) {
            $property = Config::getByName((string) $id);
            if ($property) {
                $properties[] = $property;
            }
        }

        $properties = $this->applyFilter($properties);
        $properties = $this->applyOrder($properties);
        $this->model->setTags($properties);

        return $properties;
    }

    public function getTotalCount(): int
    {
        return count($this->load());
    }

    /**
     * Apply the bundle's historical array filter format against normalized config data.
     *
     * @param Config[] $properties
     *
     * @return Config[]
     */
    private function applyFilter(array $properties): array
    {
        $filter = $this->model->getFilter();
        if ($filter === []) {
            return $properties;
        }

        return array_values(array_filter($properties, function (Config $property) use ($filter): bool {
            foreach ($filter as $field => $expectedValue) {
                if ($this->extractComparableValue($property, (string) $field) != $expectedValue) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Apply field => direction sorting against normalized config data.
     *
     * @param Config[] $properties
     *
     * @return Config[]
     */
    private function applyOrder(array $properties): array
    {
        $order = $this->model->getOrder();
        if ($order === []) {
            return $properties;
        }

        usort($properties, function (Config $left, Config $right) use ($order): int {
            foreach ($order as $field => $direction) {
                $leftValue = $this->extractComparableValue($left, (string) $field);
                $rightValue = $this->extractComparableValue($right, (string) $field);

                if ($leftValue == $rightValue) {
                    continue;
                }

                $comparison = $leftValue <=> $rightValue;

                return strtoupper((string) $direction) === 'DESC' ? -$comparison : $comparison;
            }

            return 0;
        });

        return $properties;
    }

    private function extractComparableValue(Config $property, string $field): mixed
    {
        $storageField = TagConfigNormalizer::normalizeFieldNameForStorage($field);
        $storageData = TagConfigNormalizer::normalizeForStorage($property->getObjectVars());

        return $storageData[$storageField] ?? null;
    }
}
