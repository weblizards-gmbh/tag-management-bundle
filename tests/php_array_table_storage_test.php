<?php

declare(strict_types=1);

use Weblizards\TagManagementBundle\Model\Dao\PhpArrayTableStorage;

require_once __DIR__ . '/../vendor/autoload.php';

$storageFile = sys_get_temp_dir() . '/tag-management-php-array-storage-test.php';
@unlink($storageFile);

$storage = new PhpArrayTableStorage($storageFile);

$storage->insertOrUpdate(['name' => 'b-tag', 'site_id' => 'default'], 'b-tag');
$storage->insertOrUpdate(['name' => 'a-tag', 'site_id' => 'special'], 'a-tag');

$row = $storage->getById('b-tag');
assert($row['id'] === 'b-tag', 'Expected inserted rows to expose their id.');
assert($row['name'] === 'b-tag', 'Expected inserted rows to be readable by id.');

$filtered = $storage->fetchAll(['site_id' => 'default']);
assert(count($filtered) === 1, 'Expected equality filter to return matching rows only.');
assert($filtered[0]['id'] === 'b-tag', 'Expected filtered row to match requested site_id.');

$ordered = $storage->fetchAll([], ['name' => 'ASC']);
assert(count($ordered) === 2, 'Expected both rows to be returned for unfiltered fetch.');
assert($ordered[0]['name'] === 'a-tag', 'Expected ASC ordering by field name.');

$storage->delete('a-tag');
$afterDelete = $storage->fetchAll();
assert(count($afterDelete) === 1, 'Expected delete() to remove persisted rows.');
assert($afterDelete[0]['id'] === 'b-tag', 'Expected non-deleted rows to remain untouched.');

@unlink($storageFile);
