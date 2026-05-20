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

pimcore.registerNS("pimcore.plugin.WeblizardsTagManagementBundle");
pimcore.plugin.WeblizardsTagManagementBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.WeblizardsTagManagementBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        const user = pimcore.globalmanager.get("user");
        if (user.isAllowed("tag_snippet_management")) {
            const toolbar = pimcore.globalmanager.get("layout_toolbar");

            toolbar.marketingMenu.add({
                text: t("wl_tagmanagement.tag_snippet_management"),
                iconCls: "pimcore_icon_tag",
                handler: function () {
                    try {
                        pimcore.globalmanager.get("tagmanagement").activate();
                    }
                    catch (e) {
                        pimcore.globalmanager.add("tagmanagement", new pimcore.settings.tagmanagement.panel());
                    }
                }
            });
        }
    }
});

// Prefer the global Routing object when the admin setup exposes it (for example via FOSJsRoutingBundle).
// Fall back to the bundle's stable admin paths so the UI keeps working without introducing a hard dependency.
pimcore.plugin.WeblizardsTagManagementBundle.route = function (name) {
    if (typeof Routing !== "undefined" && Routing && typeof Routing.generate === "function") {
        return Routing.generate(name);
    }

    var fallbackRoutes = {
        weblizards_tagmanagement_tree: "/admin/tag-management/tree",
        weblizards_tagmanagement_get: "/admin/tag-management/get",
        weblizards_tagmanagement_add: "/admin/tag-management/add",
        weblizards_tagmanagement_update: "/admin/tag-management/update",
        weblizards_tagmanagement_delete: "/admin/tag-management/delete"
    };

    if (!fallbackRoutes[name]) {
        throw new Error("Unknown tag management route: " + name);
    }

    return fallbackRoutes[name];
};

var WeblizardsTagManagementBundlePlugin = new pimcore.plugin.WeblizardsTagManagementBundle();
