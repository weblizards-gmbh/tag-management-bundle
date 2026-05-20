<?php

declare(strict_types=1);

use Weblizards\TagManagementBundle\Model\Tag\Config\Listing;

require_once __DIR__ . '/../vendor/autoload.php';

$listing = new Listing();
$method = new ReflectionMethod(Listing::class, 'getCacheKey');
$method->setAccessible(true);

$defaultKey = $method->invoke($listing);

$listing->setFilter(['siteId' => 1]);
$filterKey = $method->invoke($listing);

assert($defaultKey !== $filterKey, 'Expected cache key to change when filter changes.');

$listing->setOrder(['name' => 'ASC']);
$orderKey = $method->invoke($listing);

assert($filterKey !== $orderKey, 'Expected cache key to change when order changes.');
