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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Weblizards\TagManagementBundle\Model\Tag;
use Weblizards\TagManagementBundle\Service\TagInjectionService;

class TagManagerListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;
    use ResponseInjectionTrait;
    use PreviewRequestTrait;

    /** @var bool */
    protected $enabled = true;

    /** @var EditmodeResolver */
    private $editmodeResolver;

    private TagInjectionService $injectionService;

    public function __construct(EditmodeResolver $editmodeResolver, TagInjectionService $injectionService)
    {
        $this->editmodeResolver = $editmodeResolver;
        $this->injectionService = $injectionService;
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
        $editmode = $this->editmodeResolver->isEditmode($request);
        $content = $this->injectionService->inject($content, $tags, $request, $editmode);

        $response->setContent($content);
    }
}
