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

namespace Weblizards\TagManagementBundle\EventListener;

use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PreviewRequestTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\ResponseInjectionTrait;
use Pimcore\Http\Request\Resolver\EditmodeResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\Site;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Weblizards\TagManagementBundle\Model\Tag;
use Weblizards\TagManagementBundle\Service\HtmlInsertionService;

class TagManagerListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;
    use ResponseInjectionTrait;
    use PreviewRequestTrait;

    /** @var bool */
    protected $enabled = true;

    /** @var EditmodeResolver */
    private $editmodeResolver;

    public function __construct(EditmodeResolver $editmodeResolver)
    {
        $this->editmodeResolver = $editmodeResolver;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * @return bool
     */
    public function disable()
    {
        $this->enabled = false;

        return true;
    }

    /**
     * @return bool
     */
    public function enable()
    {
        $this->enabled = true;

        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        if ($this->isPreviewRequest($request)) {
            return;
        }

        $response = $event->getResponse();
        if (!$this->isHtmlResponse($response) || !$this->isEnabled()) {
            return;
        }

        $list = new Tag\Config\Listing();
        $tags = $list->load();

        if (empty($tags)) {
            return;
        }

        $content = $response->getContent();
        $requestParams = array_merge($_GET, $_POST);
        $htmlInserter = new HtmlInsertionService();

        $editmode = $this->editmodeResolver->isEditmode($request);

        /** @var Tag\Config $tag */
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

            $requestPath = rtrim($request->getPathInfo(), '/');

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
                    foreach ($tag->getItems() as $itemKey => $item) {
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
                                $content = $htmlInserter->insert(
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

        $response->setContent($content);
    }
}
