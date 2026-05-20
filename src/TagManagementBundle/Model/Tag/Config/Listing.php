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
use Weblizards\TagManagementBundle\Model\Tag\Config;

/**
 * @method \Weblizards\TagManagementBundle\Model\Tag\Config\Listing\Dao getDao()
 * @method Config[]                                                     load()
 * @method int                                                          getTotalCount()
 */
class Listing extends Model\Listing\JsonListing
{
    /** @var null|Config[] */
    protected ?array $tags;

    /**
     * @return Config[]
     * @throws \Exception
     */
    public function getTags(): array
    {
        if (null === $this->tags) {
            $cacheKey = $this->getCacheKey();
            $cached = Cache::load($cacheKey);
            if (false !== $cached) {
                $this->tags = $cached;

                return $this->tags;
            }

            $this->getDao()->load();
            Cache::save($this->tags, $cacheKey, ['tagmanagement']);
        }

        return $this->tags;
    }

    /**
     * @param Config[] $tags
     *
     * @return $this
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    private function getCacheKey(): string
    {
        return 'tagmanagement_config_listing_' . md5(serialize([
            $this->getFilter(),
            $this->getOrder(),
        ]));
    }
}
