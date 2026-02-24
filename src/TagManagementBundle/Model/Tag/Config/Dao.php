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

namespace Weblizards\TagManagementBundle\Model\Tag\Config;

use Pimcore\Cache;
use Pimcore\Model;

/**
 * @property \Weblizards\TagManagementBundle\Model\Tag\Config $model
 */
class Dao extends Model\Dao\PhpArrayTable
{
    public function configure()
    {
        parent::configure();
        $this->setFile('tag-manager');
    }

    public function getByName(?string $id = null): bool
    {
        if (null !== $id) {
            $this->model->setName($id);
        }

        $data = $this->db->getById($this->model->getName());

        if (!isset($data['id'])) {
            return false;
        }

        $this->assignVariablesToModel($data);
        $this->model->setName($data['id']);

        return true;
    }

    /**
     * @throws \Exception
     */
    public function save(): void
    {
        $ts = time();
        if (!$this->model->getCreationDate()) {
            $this->model->setCreationDate($ts);
        }
        $this->model->setModificationDate($ts);

        $dataRaw = $this->model->getObjectVars();
        $data = [];
        $allowedProperties = ['name', 'description', 'disabled', 'items', 'siteId', 'urlPattern', 'textPattern',
            'httpMethod', 'params', 'creationDate', 'modificationDate', ];

        foreach ($dataRaw as $key => $value) {
            if (in_array($key, $allowedProperties)) {
                $data[$key] = $value;
            }
        }
        $this->db->insertOrUpdate($data, $this->model->getName());
        Cache::clearTags(['tagmanagement', 'output']);
    }

    /**
     * Deletes object from database.
     */
    public function delete()
    {
        $this->db->delete($this->model->getName());
    }
}
