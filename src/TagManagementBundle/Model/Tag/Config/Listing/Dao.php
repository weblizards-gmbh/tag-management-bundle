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

use Pimcore\Model;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Model\Tag\Config\Listing;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;

/**
 * @property Listing $model
 */
class Dao extends Model\Dao\PhpArrayTable
{
    public function configure(): void
    {
        parent::configure();
        $this->setFile('tag-manager');
    }

    /**
     * Load matching tag configs and keep filter/order handling compatible with normalized storage keys.
     *
     * @throws \Exception
     */
    public function load(): array
    {
        $properties = [];
        $propertiesData = $this->db->fetchAll(
            $this->normalizeFieldMap($this->model->getFilter()),
            $this->normalizeFieldMap($this->model->getOrder())
        );

        foreach ($propertiesData as $propertyData) {
            $property = Config::getByName($propertyData['id']);
            if ($property) {
                $properties[] = $property;
            }
        }

        $this->model->setTags($properties);

        return $properties;
    }

    public function getTotalCount(): int
    {
        $data = $this->db->fetchAll(
            $this->normalizeFieldMap($this->model->getFilter()),
            $this->normalizeFieldMap($this->model->getOrder())
        );

        return count($data);
    }

    /**
     * Normalize filter/order maps so callers can keep using model field names during the migration.
     */
    private function normalizeFieldMap(array $fieldMap): array
    {
        $normalized = [];

        foreach ($fieldMap as $field => $value) {
            $normalized[TagConfigNormalizer::normalizeFieldNameForStorage((string) $field)] = $value;
        }

        return $normalized;
    }
}
