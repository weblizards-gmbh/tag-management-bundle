<?php

declare(strict_types=1);

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Model\Tag\TagConfigNormalizer;
use Weblizards\TagManagementBundle\Service\TagConfigDataBinder;

require_once __DIR__ . '/../vendor/autoload.php';

$serializer = new Serializer([new ObjectNormalizer()]);
$binder = new TagConfigDataBinder($serializer);

$tag = new Config();
assert($tag->getParams() === [], 'Expected new configs to start without predefined empty params.');

$data = [
    'name' => 'example',
    'description' => 'desc',
    'disabled' => false,
    'siteId' => 'default',
    'urlPattern' => '/test/',
    'textPattern' => 'needle',
    'httpMethod' => 'GET',
    'items' => [
        [
            'element' => 'body',
            'position' => 'end',
            'code' => '<script>code</script>',
            'enabledInEditmode' => true,
            'date' => '2026-02-24T12:30:00.000Z',
        ],
    ],
    'params' => [
        ['name' => 'foo', 'value' => 'bar'],
        ['name' => 'only-name'],
        ['name' => 'p2', 'value' => 'v2'],
        ['name' => 'p3', 'value' => 'v3'],
        ['name' => 'p4', 'value' => 'v4'],
        ['name' => 'p5', 'value' => 'v5'],
    ],
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
assert($items[0]['enabledInEditmode'] === true, 'Expected item editmode flag to be bound.');
assert($items[0]['date'] === '2026-02-24T12:30:00.000Z', 'Expected item date to stay in ISO format.');

$params = $tag->getParams();
assert(count($params) === 6, 'Expected more than five params to be bound.');
assert($params[0]['name'] === 'foo' && $params[0]['value'] === 'bar', 'Expected first param to be bound.');
assert($params[1]['name'] === 'only-name' && $params[1]['value'] === '', 'Expected missing value to default to empty string.');
assert($params[5]['name'] === 'p5' && $params[5]['value'] === 'v5', 'Expected sixth param to be bound.');

$legacyTag = new Config();
$legacyData = [
    'name' => 'legacy',
    'item.0.element' => 'head',
    'item.0.position' => 'end',
    'item.0.code' => '<script>legacy</script>',
    'item.0.enabledInEditmode' => false,
    'item.0.date' => '2026-02-24T00:00:00.000Z',
    'item.0.time' => '2026-02-24T12:30:00.000Z',
    'params.name0' => 'legacy-name',
    'params.value0' => 'legacy-value',
    'params.name1' => '',
];

$binder->bind($legacyTag, $legacyData);

$legacyItems = $legacyTag->getItems();
assert(count($legacyItems) === 1, 'Expected legacy item payload to be supported.');
assert($legacyItems[0]['date'] === '2026-02-24T12:30:00+00:00', 'Expected legacy date and time to be combined into ISO-8601.');

$legacyParams = $legacyTag->getParams();
assert(count($legacyParams) === 1, 'Expected empty legacy params to be filtered out.');
assert($legacyParams[0]['name'] === 'legacy-name' && $legacyParams[0]['value'] === 'legacy-value', 'Expected legacy params to be normalized.');

$legacyWithoutTimeTag = new Config();
$legacyWithoutTimeData = [
    'name' => 'legacy-without-time',
    'item.0.element' => 'body',
    'item.0.position' => 'end',
    'item.0.code' => '<script>end-of-day</script>',
    'item.0.date' => '2026-02-24T00:00:00.000Z',
];

$binder->bind($legacyWithoutTimeTag, $legacyWithoutTimeData);

$legacyWithoutTimeItems = $legacyWithoutTimeTag->getItems();
assert(count($legacyWithoutTimeItems) === 1, 'Expected legacy item without time to be supported.');
assert($legacyWithoutTimeItems[0]['date'] === '2026-02-24T23:59:59+00:00', 'Expected missing legacy time to default to end of day.');

$snakeCaseTag = new Config();
$snakeCaseData = [
    'name' => 'snake-case',
    'temporarily_disabled' => '1',
    'site_id' => 'default',
    'url_pattern' => '/snake/',
    'text_pattern' => 'text',
    'http_method' => 'POST',
    'items' => [
        [
            'element' => 'head',
            'position' => 'end',
            'code' => '<script>snake</script>',
            'temporarily_disabled' => false,
            'enabled_in_editmode' => true,
        ],
    ],
];

$binder->bind($snakeCaseTag, $snakeCaseData);

assert($snakeCaseTag->isDisabled() === true, 'Expected temporarily_disabled alias to map to disabled.');
assert($snakeCaseTag->getSiteId() === 'default', 'Expected snake_case site_id to map to siteId.');
assert($snakeCaseTag->getUrlPattern() === '/snake/', 'Expected snake_case url_pattern to map to urlPattern.');
assert($snakeCaseTag->getTextPattern() === 'text', 'Expected snake_case text_pattern to map to textPattern.');
assert($snakeCaseTag->getHttpMethod() === 'POST', 'Expected snake_case http_method to map to httpMethod.');
assert($snakeCaseTag->getItems()[0]['enabledInEditmode'] === true, 'Expected enabled_in_editmode alias to map to enabledInEditmode.');

$formattedCodeTag = new Config();
$formattedCodeData = [
    'name' => 'formatted-code',
    'items' => [
        [
            'code' => "  \n<script>\n  console.log('keep formatting');\n</script>\n  ",
            'element' => 'body',
            'position' => 'end',
        ],
    ],
];

$binder->bind($formattedCodeTag, $formattedCodeData);

assert(
    $formattedCodeTag->getItems()[0]['code'] === "  \n<script>\n  console.log('keep formatting');\n</script>\n  ",
    'Expected snippet code formatting to be preserved verbatim.'
);

$normalizedStorage = TagConfigNormalizer::normalizeForStorage([
    'name' => 'normalized',
    'description' => 'desc',
    'disabled' => false,
    'siteId' => 'default',
    'urlPattern' => '/normalized/',
    'textPattern' => 'needle',
    'httpMethod' => 'GET',
    'items' => [
        [
            'code' => '<script>normalized</script>',
            'element' => 'body',
            'position' => 'end',
            'disabled' => false,
            'enabledInEditmode' => true,
            'date' => '2026-02-24T12:30:00.000Z',
        ],
    ],
    'params' => [
        ['name' => 'foo', 'value' => 'bar'],
    ],
]);

assert(array_key_exists('site_id', $normalizedStorage), 'Expected storage payload to use snake_case site_id.');
assert(array_key_exists('url_pattern', $normalizedStorage), 'Expected storage payload to use snake_case url_pattern.');
assert(array_key_exists('http_method', $normalizedStorage), 'Expected storage payload to use snake_case http_method.');
assert(array_key_exists('enabled_in_editmode', $normalizedStorage['items'][0]), 'Expected item storage payload to use snake_case enabled_in_editmode.');
assert(TagConfigNormalizer::normalizeFieldNameForStorage('siteId') === 'site_id', 'Expected field aliases to normalize to snake_case storage keys.');
assert(TagConfigNormalizer::normalizeFieldNameForStorage('disabled') === 'disabled', 'Expected canonical disabled field to remain unchanged.');
