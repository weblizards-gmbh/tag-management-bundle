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

namespace Weblizards\TagManagementBundle\Model\Dao;

final class PhpArrayTableStorage
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function exists(): bool
    {
        return is_file($this->filePath);
    }

    /**
     * Load one entry by id from the PHP array file.
     */
    public function getById(string $id): array
    {
        $data = $this->loadAll();

        return is_array($data[$id] ?? null) ? $data[$id] : [];
    }

    /**
     * Insert or replace one entry and persist the whole table atomically.
     */
    public function insertOrUpdate(array $data, string $id): void
    {
        $table = $this->loadAll();
        $data['id'] = $id;
        $table[$id] = $data;

        $this->persistAll($table);
    }

    /**
     * Delete one entry and persist the updated table.
     */
    public function delete(string $id): void
    {
        $table = $this->loadAll();
        unset($table[$id]);

        $this->persistAll($table);
    }

    public function deleteFile(): void
    {
        if ($this->exists()) {
            unlink($this->filePath);
        }
    }

    /**
     * Return all rows after applying simple equality filters and field-based ordering.
     */
    public function fetchAll(array $filter = [], array $order = []): array
    {
        $rows = array_values($this->loadAll());

        if ($filter !== []) {
            $rows = array_values(array_filter($rows, function (mixed $row) use ($filter): bool {
                if (!is_array($row)) {
                    return false;
                }

                foreach ($filter as $field => $expectedValue) {
                    if (($row[$field] ?? null) != $expectedValue) {
                        return false;
                    }
                }

                return true;
            }));
        }

        if ($order !== []) {
            usort($rows, static function (array $left, array $right) use ($order): int {
                foreach ($order as $field => $direction) {
                    $leftValue = $left[$field] ?? null;
                    $rightValue = $right[$field] ?? null;

                    if ($leftValue == $rightValue) {
                        continue;
                    }

                    $comparison = $leftValue <=> $rightValue;

                    return strtoupper((string) $direction) === 'DESC' ? -$comparison : $comparison;
                }

                return 0;
            });
        }

        return $rows;
    }

    /**
     * Load the raw keyed table from disk. Invalid or missing files are treated as empty tables.
     */
    private function loadAll(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $data = include $this->filePath;

        return is_array($data) ? $data : [];
    }

    /**
     * Persist the complete keyed table in the legacy PHP array format used by the bundle.
     */
    private function persistAll(array $table): void
    {
        ksort($table);

        $directory = dirname($this->filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $tmpFile = $this->filePath . '.tmp';
        $content = "<?php\n\nreturn " . var_export($table, true) . ";\n";

        file_put_contents($tmpFile, $content, LOCK_EX);
        rename($tmpFile, $this->filePath);
    }
}
