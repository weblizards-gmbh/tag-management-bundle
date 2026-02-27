<?php

declare(strict_types=1);

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Service\TagConfigDataBinder;

require_once __DIR__ . '/../vendor/autoload.php';

$serializer = new Serializer([new ObjectNormalizer()]);
$binder = new TagConfigDataBinder($serializer);

$tag = new Config();
$data = [
    'name' => 'example',
    'description' => 'desc',
    'disabled' => false,
    'siteId' => 'default',
    'urlPattern' => '/test/',
    'textPattern' => 'needle',
    'httpMethod' => 'GET',
    'item.0.element' => 'body',
    'item.0.position' => 'end',
    'item.0.code' => '<script>code</script>',
    'item.0.enabledInEditmode' => true,
    'item.0.date' => '2026-02-24T00:00:00.000Z',
    'item.0.time' => '2026-02-24T12:30:00.000Z',
    'params.name0' => 'foo',
    'params.value0' => 'bar',
    'params.name1' => 'only-name',
];

$binder->bind($tag, $data);

assert($tag->getName() === 'example', 'Expected name to be bound.');
assert($tag->getDescription() === 'desc', 'Expected description to be bound.');
assert($tag->getUrlPattern() === '/test/', 'Expected urlPattern to be bound.');
assert($tag->getTextPattern() === 'needle', 'Expected textPattern to be bound.');
assert($tag->getHttpMethod() === 'GET', 'Expected httpMethod to be bound.');

$items = $tag->getItems();
assert(count($items) === 1, 'Expected one item to be bound.');
assert($items[0]['element'] === 'body', 'Expected item element to be bound.');
assert($items[0]['position'] === 'end', 'Expected item position to be bound.');
assert($items[0]['code'] === '<script>code</script>', 'Expected item code to be bound.');
assert(isset($items[0]['date']) && is_int($items[0]['date']), 'Expected item date to be converted to timestamp.');

$params = $tag->getParams();
assert(count($params) === 2, 'Expected two params to be bound.');
assert($params[0]['name'] === 'foo' && $params[0]['value'] === 'bar', 'Expected first param to be bound.');
assert($params[1]['name'] === 'only-name' && $params[1]['value'] === '', 'Expected missing value to default to empty string.');
