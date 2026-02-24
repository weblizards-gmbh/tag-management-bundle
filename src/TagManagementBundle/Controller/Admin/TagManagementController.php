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

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Weblizards\TagManagementBundle\Model\Tag;

/**
 * @Route("/admin/tag-management")
 */
class TagManagementController extends AdminController
{
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

        return $this->adminJson($tags);
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

        $tag = Tag\Config::getByName($request->get('name'));

        if (!$tag) {
            $tag = new Tag\Config();
            $tag->setName($request->get('name'));
            $tag->save();

            $success = true;
        }

        return $this->adminJson(['success' => $success, 'id' => $tag->getName()]);
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

        return $this->adminJson(['success' => true]);
    }

    /**
     * @Route("/get", name="weblizards_tagmanagement_get", methods={"GET"}, options={"expose"=true})
     */
    public function getAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $tag = Tag\Config::getByName($request->get('name'));

        return $this->adminJson($tag);
    }

    /**
     * @Route("/update", name="weblizards_tagmanagement_update", methods={"PUT"}, options={"expose"=true})
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->checkPermission('tag_snippet_management');

        $tag = Tag\Config::getByName($request->get('name'));
        $data = $this->decodeJson($request->get('configuration'));

        $items = [];
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($tag, $setter)) {
                $tag->{$setter}($value);
            }

            if (0 === strpos($key, 'item.')) {
                $cleanKeyParts = explode('.', $key);

                if ('date' == $cleanKeyParts[2]) {
                    $date = $value;
                    $value = null;

                    if (!empty($date) && !empty($data[$cleanKeyParts[0] . '.' . $cleanKeyParts[1] . '.time'])) {
                        $time = $data[$cleanKeyParts[0] . '.' . $cleanKeyParts[1] . '.time'];
                        $time = explode('T', $time);
                        $date = explode('T', $date);
                        $value = strtotime($date[0] . 'T' . $time[1]);
                    }
                } elseif ('time' == $cleanKeyParts[2]) {
                    continue;
                }

                $items[$cleanKeyParts[1]][$cleanKeyParts[2]] = $value;
            }
        }

        $tag->resetItems();
        foreach ($items as $item) {
            $tag->addItem($item);
        }

        // parameters get/post
        $params = [];
        for ($i = 0; $i < 5; ++$i) {
            if (isset($data['params.name' . $i])) {
                $params[] = [
                    'name' => $data['params.name' . $i],
                    'value' => $data['params.value' . $i],
                ];
            }
        }
        $tag->setParams($params);

        $oldName = $request->get('name');
        $newName = $data['name'];

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

            return $this->adminJson(['success' => true]);
        } catch (\Exception $e) {
            return $this->adminJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
