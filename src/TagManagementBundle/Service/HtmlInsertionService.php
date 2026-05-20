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

namespace Weblizards\TagManagementBundle\Service;

use Masterminds\HTML5;
use Symfony\Component\DomCrawler\Crawler;

class HtmlInsertionService
{
    public function insert(string $content, string $selector, string $position, string $code): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($content);
        $crawler = new Crawler($dom);

        $node = $crawler->filter($selector);
        if ($node->count() === 0) {
            return $content;
        }

        $element = $node->first()->getNode(0);
        if (!$element instanceof \DOMElement) {
            return $content;
        }

        $fragmentHtml = "\n\n" . $code . "\n\n";
        $fragmentNodes = $html5->loadHTMLFragment($fragmentHtml);
        if ('end' === $position) {
            foreach ($fragmentNodes as $fragmentNode) {
                $element->appendChild($element->ownerDocument->importNode($fragmentNode, true));
            }
        } else {
            $referenceNode = $element->firstChild;
            foreach ($fragmentNodes as $fragmentNode) {
                $element->insertBefore($element->ownerDocument->importNode($fragmentNode, true), $referenceNode);
            }
        }

        return $html5->saveHTML($dom);
    }
}
