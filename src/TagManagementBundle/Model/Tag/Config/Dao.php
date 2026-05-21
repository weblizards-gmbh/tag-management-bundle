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
use Pimcore\Model\Dao\PimcoreLocationAwareConfigDao;
use Weblizards\TagManagementBundle\Model\Dao\PhpArrayTableStorage;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;

/**
 * @property \Weblizards\TagManagementBundle\Model\Tag\Config $model
 */
class Dao extends PimcoreLocationAwareConfigDao
{
    protected const SETTINGS_STORE_SCOPE = 'weblizards_tagmanagement';

    private ?PhpArrayTableStorage $legacyStorage = null;

    public function configure(): void
    {
        parent::configure([
            'containerConfig' => [],
            'settingsStoreScope' => self::SETTINGS_STORE_SCOPE,
            'storageConfig' => [
                'read_target' => [
                    'type' => 'settings-store',
                ],
                'write_target' => [
                    'type' => 'settings-store',
                    'options' => [],
                ],
            ],
        ]);

        $this->legacyStorage = new PhpArrayTableStorage($this->resolveLegacyStoragePath());
    }

    /**
     * Load one tag config from the Pimcore settings store and fall back to the legacy PHP array file during migration.
     */
    public function getByName(?string $id = null): bool
    {
        if (null !== $id) {
            $this->model->setName($id);
        }

        $data = $this->getDataByName($this->model->getName());
        if (!$data) {
            $data = $this->getLegacyRowById($this->model->getName());
        }

        if (!$data) {
            return false;
        }

        $data['id'] = $data['id'] ?? $this->model->getName();
        $data = TagConfigNormalizer::normalizeForModel($data);
        $this->assignVariablesToModel($data);
        $this->model->setName($data['name']);

        return true;
    }

    /**
     * Persist the config in normalized snake_case storage format while keeping the model API stable.
     *
     * @throws \Exception
     */
    public function save(): void
    {
        $ts = time();
        if (!$this->model->getCreationDate()) {
            $this->model->setCreationDate($ts);
        }
        $this->model->setModificationDate($ts);

        $data = TagConfigNormalizer::normalizeForStorage($this->model->getObjectVars());
        $this->saveData($this->model->getName(), $data);

        if ($this->legacyStorage !== null && $this->legacyStorage->getById($this->model->getName()) !== []) {
            $this->legacyStorage->delete($this->model->getName());
        }

        Cache::clearTags(['tagmanagement', 'output']);
    }

    /**
     * Deletes object from database.
     */
    public function delete(): void
    {
        $name = $this->model->getName();

        if ($this->existsInSettingsStore($name)) {
            $this->deleteData($name);
        }

        if ($this->legacyStorage !== null && $this->legacyStorage->getById($name) !== []) {
            $this->legacyStorage->delete($name);
        }
    }

    /**
     * Distinguish persisted settings-store data from legacy file fallback reads during migration.
     */
    public function existsInSettingsStore(string $id): bool
    {
        return (bool) $this->getDataByName($id);
    }

    /**
     * Expose legacy rows to the listing DAO so Pimcore 11 upgrades can remain readable before migration runs.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getLegacyRows(): array
    {
        return $this->legacyStorage?->fetchAll() ?? [];
    }

    protected function getLegacyRowById(string $id): array
    {
        return $this->legacyStorage?->getById($id) ?? [];
    }

    protected function resolveLegacyStoragePath(): string
    {
        $projectDir = \Pimcore::hasKernel() && \Pimcore::getKernel() !== null
            ? \Pimcore::getKernel()->getProjectDir()
            : dirname(__DIR__, 5);

        return $projectDir . '/var/config/tag-manager.php';
    }
}
