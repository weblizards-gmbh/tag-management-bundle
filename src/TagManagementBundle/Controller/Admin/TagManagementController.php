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

namespace Weblizards\TagManagementBundle\Controller\Admin;

use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Weblizards\TagManagementBundle\Model\Tag;
use Weblizards\TagManagementBundle\Service\TagConfigDataBinder;

/**
 * @Route("/admin/tag-management")
 */
class TagManagementController extends UserAwareController
{
    use JsonHelperTrait;

    private const TAG_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/tree", name="weblizards_tagmanagement_tree", methods={"GET", "POST"}, options={"expose"=true})
     */
    public function treeAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $tags = [];

        $list = new Tag\Config\Listing();
        $items = $list->load();

        foreach ($items as $item) {
            $tags[] = [
                'id' => $item->getName(),
                'text' => $item->getName(),
            ];
        }

        return $this->jsonResponse($tags);
    }

    /**
     * @Route("/add", name="weblizards_tagmanagement_add", methods={"POST"}, options={"expose"=true})
     *
     * @throws \Exception
     */
    public function addAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $success = false;
        $name = $this->normalizeTagName($request->get('name'));

        if (!$this->isValidTagName($name)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $this->translateAdmin('wl_tagmanagement.invalid_tag_name'),
            ]);
        }

        $tag = Tag\Config::getByName($name);

        if (!$tag) {
            $tag = new Tag\Config();
            $tag->setName($name);
            $tag->save();

            $success = true;
        }

        return $this->jsonResponse(['success' => $success, 'id' => $tag->getName()]);
    }

    /**
     * @Route("/delete", name="weblizards_tagmanagement_delete", methods={"DELETE"}, options={"expose"=true})
     */
    public function deleteAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $tag = Tag\Config::getByName($request->get('name'));
        if ($tag) {
            $tag->delete();
        }

        return $this->jsonResponse(['success' => true]);
    }

    /**
     * @Route("/get", name="weblizards_tagmanagement_get", methods={"GET"}, options={"expose"=true})
     */
    public function getAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $tag = Tag\Config::getByName($this->normalizeTagName($request->get('name')));
        if ($tag === null) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $this->translateAdmin('wl_tagmanagement.tag_not_found'),
            ]);
        }

        return $this->jsonResponse($tag);
    }

    /**
     * @Route("/update", name="weblizards_tagmanagement_update", methods={"PUT"}, options={"expose"=true})
     */
    public function updateAction(Request $request, TagConfigDataBinder $dataBinder): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $oldName = $this->normalizeTagName($request->get('name'));
        $tag = Tag\Config::getByName($oldName);
        if ($tag === null) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $this->translateAdmin('wl_tagmanagement.tag_not_found'),
            ]);
        }

        $data = $this->decodeJson($request->get('configuration'));

        $dataBinder->bind($tag, $data);

        $newName = $this->normalizeTagName($tag->getName());

        if (!$this->isValidTagName($newName)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $this->translateAdmin('wl_tagmanagement.invalid_tag_name'),
            ]);
        }

        if ($oldName !== $newName && Tag\Config::getByName($newName) !== null) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $this->translateAdmin('wl_tagmanagement.name_already_in_use'),
            ]);
        }

        try {
            if ($oldName !== $newName) {
                // First write under the new name, then delete the old one to avoid data loss on failure.
                $tag->setName($newName);
                $tag->save();

                $oldTag = Tag\Config::getByName($oldName);
                if ($oldTag) {
                    $oldTag->delete();
                }
            } else {
                $tag->save();
            }

            return $this->jsonResponse([
                'success' => true,
                'id' => $newName,
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Keep admin-side validation aligned with the ExtJS UI so invalid rename requests fail server-side too.
     */
    private function isValidTagName(string $name): bool
    {
        return $name !== '' && preg_match(self::TAG_NAME_PATTERN, $name) === 1;
    }

    private function normalizeTagName(?string $name): string
    {
        // Keep server-side rename checks consistent with the add-dialog and ExtJS save flow.
        return trim((string) $name);
    }

    /**
     * Resolve admin translation keys for JSON error responses consumed by the ExtJS backend UI.
     */
    private function translateAdmin(string $key): string
    {
        return $this->translator->trans($key, [], Translation::DOMAIN_ADMIN);
    }
}
