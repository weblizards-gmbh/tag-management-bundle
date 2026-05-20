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

namespace Weblizards\TagManagementBundle\Command;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Weblizards\TagManagementBundle\Model\Tag\Config;

class MigrateTagItemDatesCommand extends Command
{
    protected static $defaultName = 'weblizards:tag-management:migrate-item-dates';
    protected static $defaultDescription = 'Migrate tag item expiry dates to ISO-8601 UTC ("Z") format';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = new Config\Listing();
        $tags = $list->load();

        $tagsUpdated = 0;
        $itemsUpdated = 0;

        /** @var Config $tag */
        foreach ($tags as $tag) {
            $items = $tag->getItems();
            if (!is_array($items) || $items === []) {
                continue;
            }

            $changed = false;

            foreach ($items as $index => $item) {
                if (!array_key_exists('date', $item)) {
                    continue;
                }

                $original = $item['date'];
                $normalized = $this->normalizeDateValue($original);

                if ($normalized !== $original) {
                    $items[$index]['date'] = $normalized;
                    $itemsUpdated++;
                    $changed = true;
                }
            }

            if ($changed) {
                $tag->setItems($items);
                $tag->save();
                $tagsUpdated++;
            }
        }

        $output->writeln(sprintf(
            'Tag items migrated. Tags updated: %d, items updated: %d.',
            $tagsUpdated,
            $itemsUpdated
        ));

        return Command::SUCCESS;
    }

    /**
     * @return string|null
     */
    private function normalizeDateValue($value)
    {
        if (empty($value) || $value === '0' || $value === 0) {
            return null;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $timestamp = (int) $value;
            if ($timestamp <= 0) {
                return null;
            }

            return Carbon::createFromTimestampUTC($timestamp)->format('Y-m-d\TH:i:s\Z');
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }
}
