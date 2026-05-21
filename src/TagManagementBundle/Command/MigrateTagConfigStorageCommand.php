<?php
/**
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Weblizards GmbH (https://www.weblizards.de)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace Weblizards\TagManagementBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Weblizards\TagManagementBundle\Model\Dao\PhpArrayTableStorage;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;

class MigrateTagConfigStorageCommand extends Command
{
    protected static $defaultName = 'weblizards:tag-management:migrate-config-storage';
    protected static $defaultDescription = 'Migrate legacy tag-manager.php configs into the Pimcore settings store';

    private PhpArrayTableStorage $legacyStorage;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir
    ) {
        parent::__construct();
        $this->legacyStorage = new PhpArrayTableStorage($projectDir . '/var/config/tag-manager.php');
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Inspect legacy configs without writing to the settings store')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite settings-store entries that already exist')
            ->addOption('cleanup-legacy-file', null, InputOption::VALUE_NONE, 'Delete the legacy tag-manager.php file after a successful migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->legacyStorage->exists()) {
            $output->writeln('No legacy tag-manager.php file found.');

            return Command::SUCCESS;
        }

        $rows = $this->legacyStorage->fetchAll();
        if ($rows === []) {
            $output->writeln('Legacy tag-manager.php exists but contains no tag configs.');

            if ($input->getOption('cleanup-legacy-file') && !$input->getOption('dry-run')) {
                $this->legacyStorage->deleteFile();
                $output->writeln('Removed empty legacy tag-manager.php file.');
            }

            return Command::SUCCESS;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $migrated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $normalized = TagConfigNormalizer::normalizeForModel($row);
            $name = $normalized['name'] ?? '';

            if ($name === '') {
                $skipped++;
                $output->writeln('Skipped one legacy row without a usable tag name.');
                continue;
            }

            $existingConfig = new Config();
            $existingInSettingsStore = $existingConfig->getDao()->existsInSettingsStore($name);

            if ($existingInSettingsStore && !$overwrite) {
                $skipped++;
                $output->writeln(sprintf('Skipped existing settings-store entry "%s".', $name));
                continue;
            }

            if ($dryRun) {
                $migrated++;
                $output->writeln(sprintf('Would migrate "%s".', $name));
                continue;
            }

            $config = new Config();
            $config->setValues($normalized, true);
            $config->setName($name);
            $config->save();

            $migrated++;
            $output->writeln(sprintf('Migrated "%s".', $name));
        }

        if ($input->getOption('cleanup-legacy-file') && !$dryRun) {
            $this->legacyStorage->deleteFile();
            $output->writeln('Removed legacy tag-manager.php file.');
        }

        $output->writeln(sprintf(
            'Config storage migration finished. Migrated: %d, skipped: %d.',
            $migrated,
            $skipped
        ));

        return Command::SUCCESS;
    }
}
