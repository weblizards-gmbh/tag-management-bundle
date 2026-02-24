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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Weblizards\TagManagementBundle\Service\TagExpiryService;

class DisableExpiredTagItemsCommand extends Command
{
    protected static $defaultName = 'weblizards:tag-management:disable-expired-items';
    protected static $defaultDescription = 'protected static $defaultName';

    private TagExpiryService $tagExpiryService;

    public function __construct(TagExpiryService $tagExpiryService)
    {
        parent::__construct();
        $this->tagExpiryService = $tagExpiryService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->tagExpiryService->disableExpiredItems();

        $output->writeln(sprintf(
            'Expired tag items disabled. Tags updated: %d, items disabled: %d.',
            $result['tags_updated'],
            $result['items_disabled']
        ));

        return Command::SUCCESS;
    }
}
