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
     * @throws \Exception
     */
    public function load(): array
    {
        $properties = [];
        $propertiesData = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

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
        $data = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

        return count($data);
    }
}
