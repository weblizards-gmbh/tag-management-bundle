<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;
use Weblizards\TagManagementBundle\Model\Tag\Config;
use Weblizards\TagManagementBundle\Service\HtmlInsertionService;
use Weblizards\TagManagementBundle\Service\TagInjectionService;

require_once __DIR__ . '/../vendor/autoload.php';

$content = '<html><head></head><body><div id="target">Keep</div></body></html>';
$request = Request::create('/test', 'GET', ['foo' => 'bar']);

$tag = new Config();
$tag->setName('test');
$tag->setDisabled(false);
$tag->setHttpMethod('');
$tag->setSiteId('');
$tag->setUrlPattern('');
$tag->setTextPattern('');
$tag->setParams([
    ['name' => 'foo', 'value' => 'bar'],
]);

$now = time();
$tag->setItems([
    [
        'disabled' => true,
        'element' => 'head',
        'position' => 'end',
        'code' => '<script>disabled</script>',
    ],
    [
        'disabled' => false,
        'element' => 'body',
        'position' => 'end',
        'code' => '<script>expired</script>',
        'date' => $now - 10,
        'enabledInEditmode' => true,
    ],
    [
        'disabled' => false,
        'element' => 'head',
        'position' => 'end',
        'code' => '<script>ok-head</script>',
        'date' => $now + 3600,
        'enabledInEditmode' => true,
    ],
    [
        'disabled' => false,
        'element' => '#target',
        'position' => 'end',
        'code' => '<span>ok-selector</span>',
        'date' => $now + 3600,
        'enabledInEditmode' => true,
    ],
    [
        'disabled' => false,
        'element' => 'body',
        'position' => 'end',
        'code' => '<script>editmode-only</script>',
        'date' => $now + 3600,
        'enabledInEditmode' => false,
    ],
]);

$service = new TagInjectionService(new HtmlInsertionService());

$result = $service->inject($content, [$tag], $request, false);

assert(false !== strpos($result, 'ok-head'), 'Expected head insertion to be applied.');
assert(false !== strpos($result, 'ok-selector'), 'Expected selector insertion to be applied.');
assert(false === strpos($result, 'disabled'), 'Expected disabled item to be skipped.');
assert(false === strpos($result, 'expired'), 'Expected expired item to be skipped.');
assert(false === strpos($result, 'editmode-only'), 'Expected editmode-only item to be skipped when not in editmode.');
