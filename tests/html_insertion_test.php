<?php

declare(strict_types=1);

use Weblizards\TagManagementBundle\Service\HtmlInsertionService;

require_once __DIR__ . '/../vendor/autoload.php';

$service = new HtmlInsertionService();

// Insert at beginning.
$content = '<html><body><div id="target"><span>Keep</span></div></body></html>';
$result = $service->insert($content, '#target', 'start', '<script>code</script>');

$posScript = strpos($result, '<script>code</script>');
$posSpan = strpos($result, '<span>Keep</span>');

assert(false !== $posScript, 'Expected script to be present for start insertion.');
assert(false !== $posSpan, 'Expected span to be present for start insertion.');
assert($posScript < $posSpan, 'Expected script to appear before span for start insertion.');

// Insert at end.
$result = $service->insert($content, '#target', 'end', '<script>code</script>');
$posScript = strpos($result, '<script>code</script>');
$posSpan = strpos($result, '<span>Keep</span>');

assert(false !== $posScript, 'Expected script to be present for end insertion.');
assert(false !== $posSpan, 'Expected span to be present for end insertion.');
assert($posScript > $posSpan, 'Expected script to appear after span for end insertion.');

// Missing selector should return content unchanged.
$result = $service->insert($content, '#does-not-exist', 'end', '<script>code</script>');
assert($result === $content, 'Expected content to be unchanged when selector is missing.');
