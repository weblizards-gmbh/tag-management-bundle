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

use Pimcore\Cache;
use Pimcore\Model;

/**
 * @method \Weblizards\TagManagementBundle\Model\Tag\Config\Dao getDao()
 * @method void                                                 save()
 */
class Config extends Model\AbstractModel
{
    public array $items = [];

    public string $name = '';

    public string $description = '';

    public string $siteId;

    public string $urlPattern = '';

    public string $textPattern = '';

    public string $httpMethod = '';

    public bool $disabled;

    public array $params = [
        ['name' => '', 'value' => ''],
        ['name' => '', 'value' => ''],
        ['name' => '', 'value' => ''],
        ['name' => '', 'value' => ''],
        ['name' => '', 'value' => ''],
    ];

    public int $modificationDate;

    public int $creationDate;

    public static function getByName(string $name): ?Config
    {
        try {
            $tag = new self();
            $tag->getDao()->getByName($name);

            return $tag;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete from Database.
     */
    public function delete(): void
    {
        $this->getDao()->delete();

        // clear cache tags
        Cache::clearTags(['tagmanagement', 'output']);
    }

    /**
     * @param array $parameters
     *
     * @return bool
     */
    public function addItem(array $parameters): bool
    {
        $this->items[] = $parameters;

        return true;
    }

    /**
     * @return true
     */
    public function addItemAt(int $position, array $parameters): bool
    {
        array_splice($this->items, $position, 0, [$parameters]);

        return true;
    }

    public function resetItems(): void
    {
        $this->items = [];
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setItems(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setHttpMethod(string $httpMethod): static
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function setUrlPattern(string $urlPattern): static
    {
        $this->urlPattern = $urlPattern;

        return $this;
    }

    public function getUrlPattern(): string
    {
        return $this->urlPattern;
    }

    public function setSiteId(string $siteId): void
    {
        $this->siteId = $siteId;
    }

    public function getSiteId(): string
    {
        return $this->siteId;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setTextPattern(string $textPattern): static
    {
        $this->textPattern = $textPattern;

        return $this;
    }

    public function getTextPattern(): string
    {
        return $this->textPattern;
    }

    public function getModificationDate(): int
    {
        return $this->modificationDate;
    }

    public function setModificationDate(int $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }

    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    public function setCreationDate(int $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
