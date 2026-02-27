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

use Pimcore\Model\Site;
use Symfony\Component\HttpFoundation\Request;
use Weblizards\TagManagementBundle\Model\Tag\Config;

class TagInjectionService
{
    private HtmlInsertionService $htmlInserter;

    public function __construct(HtmlInsertionService $htmlInserter)
    {
        $this->htmlInserter = $htmlInserter;
    }

    /**
     * @param Config[] $tags
     */
    public function inject(string $content, array $tags, Request $request, bool $editmode): string
    {
        $requestParams = array_merge($request->query->all(), $request->request->all());
        $requestPath = rtrim($request->getPathInfo(), '/');

        foreach ($tags as $tag) {
            if ($tag->isDisabled()) {
                continue;
            }

            $method = strtolower($tag->getHttpMethod());
            $pattern = $tag->getUrlPattern();
            $textPattern = $tag->getTextPattern();

            // site check
            if (Site::isSiteRequest() && $tag->getSiteId()) {
                if (Site::getCurrentSite()->getId() != $tag->getSiteId()) {
                    continue;
                }
            } elseif (!Site::isSiteRequest() && $tag->getSiteId() && 'default' != $tag->getSiteId()) {
                continue;
            }

            if (($method == strtolower($request->getMethod()) || empty($method))
                && (empty($pattern) || @preg_match($pattern, $requestPath))
                && (empty($textPattern) || false !== strpos($content, $textPattern))
            ) {
                $paramsValid = true;
                foreach ($tag->getParams() as $param) {
                    if (!empty($param['name'])) {
                        if (!empty($param['value'])) {
                            if (!array_key_exists($param['name'], $requestParams) || $requestParams[$param['name']] != $param['value']) {
                                $paramsValid = false;
                            }
                        } else {
                            if (!array_key_exists($param['name'], $requestParams)) {
                                $paramsValid = false;
                            }
                        }
                    }
                }

                if (is_array($tag->getItems()) && $paramsValid) {
                    foreach ($tag->getItems() as $item) {
                        if ($item['disabled']) {
                            continue;
                        }

                        $currentTime = new \Carbon\Carbon();

                        if (!empty($item['date']) && $currentTime->getTimestamp() > $item['date']) {
                            // Skip expired items. Persistence is handled by a dedicated service/job.
                            continue;
                        }

                        if ($editmode && !$item['enabledInEditmode']) {
                            continue;
                        }

                        if (!empty($item['element']) && !empty($item['code']) && !empty($item['position'])) {
                            if (in_array($item['element'], ['body', 'head'])) {
                                // check if the code should be inserted using one of the presets
                                // because this can be done much faster than using a html parser
                                if ('end' == $item['position']) {
                                    $regEx = '@</' . $item['element'] . '>@i';
                                    $content = preg_replace($regEx, "\n\n" . $item['code'] . "\n\n</" . $item['element'] . '>', $content, 1);
                                } else {
                                    $regEx = '/<' . $item['element'] . '([^a-zA-Z])?( [^>]+)?>/';
                                    $content = preg_replace($regEx, '<' . $item['element'] . "$1$2>\n\n" . $item['code'] . "\n\n", $content, 1);
                                }
                            } else {
                                $content = $this->htmlInserter->insert(
                                    $content,
                                    (string) $item['element'],
                                    (string) $item['position'],
                                    (string) $item['code']
                                );
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }
}
