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

pimcore.registerNS("pimcore.settings.tagmanagement.item");

pimcore.settings.tagmanagement.item = Class.create({


    initialize: function (data, parentPanel) {
        this.parentPanel = parentPanel;
        this.data = data;
        this.currentIndex = 0;
        this.currentParamIndex = 0;

        this.addLayout();

        if(this.data.items && this.data.items.length > 0) {
            for(var i=0; i<this.data.items.length; i++) {
                this.addItem(this.data.items[i]);
            }
        }
    },


    addLayout: function () {

        this.editpanel = new Ext.Panel({
            region: "center",
            bodyStyle: "padding: 20px;",
            autoScroll: true
        });

        var panelButtons = [];
        panelButtons.push({
            text: t("wl_tagmanagement.save"),
            iconCls: "pimcore_icon_apply",
            handler: this.save.bind(this)
        });

        this.itemContainer = new Ext.Panel({
            style: "margin: 20px 0 0 0;",
            tbar: [{
                xtype: "tbtext",
                html: t("wl_tagmanagement.tags")
            }, {
                iconCls: "pimcore_icon_add",
                handler: this.addItem.bind(this)
            }],
            border: false
        });

        this.paramContainer = new Ext.Panel({
            border: false,
            items: [],
            tbar: ["->", {
                iconCls: "pimcore_icon_add",
                handler: this.addParam.bind(this)
            }]
        });

        if (this.data.params && this.data.params.length > 0) {
            for (var i = 0; i < this.data.params.length; i++) {
                this.addParam(this.data.params[i]);
            }
        } else {
            this.addParam();
        }

        var paramsFieldSet = {
            xtype: "fieldset",
            title: t("wl_tagmanagement.parameters") + " (GET &amp; POST)",
            items: [this.paramContainer],
            collapsible: true,
            collapsed: true
        };

        this.panel = new Ext.form.FormPanel({
            border: false,
            closable: true,
            autoScroll: true,
            bodyStyle: "padding: 20px;",
            title: this.data.name,
            id: "pimcore_tagmanagement_panel_" + this.data.name,
            labelWidth: 150,
            items: [{
                xtype: "textfield",
                name: "name",
                value: this.data.name,
                fieldLabel: t("wl_tagmanagement.name"),
                width: 450,
                allowBlank: false
            },{
                xtype: "textarea",
                name: "description",
                value: this.data.description,
                fieldLabel: t("wl_tagmanagement.description"),
                width: 450,
                height: 50
            },
                {
                    xtype: "checkbox",
                    fieldLabel: t("wl_tagmanagement.temporarily_disabled"),
                    name: "disabled",
                    checked: this.data.disabled
                },
                {
                xtype: "fieldset",
                title: t("wl_tagmanagement.conditions"),
                items: [{
                    xtype: "combo",
                    name: "siteId",
                    fieldLabel: t("wl_tagmanagement.site"),
                    store: pimcore.globalmanager.get("sites"),
                    valueField: "id",
                    displayField: "domain",
                    triggerAction: "all",
                    editable: false,
                    value: this.data.siteId
                },{
                    xtype: "textfield",
                    name: "urlPattern",
                    value: this.data.urlPattern,
                    fieldLabel: t("wl_tagmanagement.url_pattern"),
                    width: 550,
                    fieldCls: "input_drop_target",
                    listeners: {
                        "render": function (el) {
                            new Ext.dd.DropZone(el.getEl(), {
                                reference: el,
                                ddGroup: "element",
                                getTargetFromEvent: function(e) {
                                    return this.getEl();
                                }.bind(el),

                                onNodeOver : function(target, dd, e, data) {
                                    if (data.records.length == 1 && data.records[0].data.elementType == "document") {
                                        return Ext.dd.DropZone.prototype.dropAllowed;
                                    }
                                },

                                onNodeDrop : function (el, target, dd, e, data) {
                                    if (pimcore.helpers.dragAndDropValidateSingleItem(data)) {
                                        try {
                                            var record = data.records[0];
                                            var data = record.data;

                                            if (data.elementType == "document") {
                                                var pattern = preg_quote(data.path);
                                                pattern = str_replace("@", "\\@", pattern);
                                                pattern = "@^" + pattern + "$@";
                                                el.setValue(pattern);
                                                return true;
                                            }
                                        } catch (e) {
                                            console.log(e);
                                        }
                                    }
                                    return false;
                                }.bind(this, el)
                            });
                        }.bind(this)
                    }
                },{
                    xtype:'combo',
                    fieldLabel: t('wl_tagmanagement.http_method'),
                    name: "httpMethod",
                    store: [["",t("wl_tagmanagement.any")],["get","GET"],["post","POST"]],
                    triggerAction: "all",
                    typeAhead: false,
                    editable: false,
                    forceSelection: true,
                    mode: "local",
                    value: this.data.httpMethod,
                    width: 250
                },{
                    xtype: "textfield",
                    name: "textPattern",
                    value: this.data.textPattern,
                    fieldLabel: t("wl_tagmanagement.matching_text"),
                    width: 400
                },
                    paramsFieldSet
                ]
            }, this.itemContainer],
            buttons: panelButtons
        });


        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();
    },

    addParam: function (data) {
        data = data || {};

        var myId = Ext.id();
        var param = new Ext.Panel({
            id: myId,
            border: false,
            layout: "hbox",
            style: "border-top: none !important",
            padding: 0,
            tagParamId: myId,
            items: [{
                xtype: "textfield",
                fieldLabel: t("wl_tagmanagement.name"),
                name: "param." + myId + ".name",
                value: data.name || ""
            },{
                xtype: "textfield",
                margin: "0 0 0 20",
                fieldLabel: t("wl_tagmanagement.value"),
                name: "param." + myId + ".value",
                value: data.value || ""
            },{
                xtype: "button",
                margin: "0 0 0 10",
                iconCls: "pimcore_icon_delete",
                handler: function (paramId) {
                    this.paramContainer.remove(Ext.getCmp(paramId));
                    this.paramContainer.updateLayout();
                }.bind(this, myId)
            }]
        });

        this.paramContainer.add(param);
        this.paramContainer.updateLayout();
        this.currentParamIndex++;
    },


    addItem: function (data) {
        if(typeof data == "undefined") {
            data = {};
        }
        var myId = Ext.id();

        if (data.date) {
            if (Ext.isNumber(data.date)) {
                data.date = new Date(data.date * 1000);
            } else if (Ext.isString(data.date)) {
                var parsedDate = new Date(data.date);
                if (!isNaN(parsedDate.getTime())) {
                    data.date = parsedDate;
                }
            }
        }

        var item =  new Ext.Panel({
            id: myId,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px;",
            border: true,
            tagItemId: myId,
            tbar: ["->",{
                iconCls: "pimcore_icon_delete",
                handler: function (myId) {
                    this.itemContainer.remove(Ext.getCmp(myId));
                }.bind(this, myId)
            }],
            items: [{
                xtype: "textarea",
                width: 440,
                height: 200,
                fieldLabel: t("wl_tagmanagement.code"),
                name: "item." + myId + ".code",
                value: data.code
            },{
                xtype:'combo',
                fieldLabel: t('wl_tagmanagement.element_css_selector'),
                name: "item." + myId + ".element",
                disableKeyFilter: true,
                store: [["body","body"],["head","head"]],
                triggerAction: "all",
                mode: "local",
                value: data.element,
                width: 250
            },{
                xtype:'combo',
                fieldLabel: t('wl_tagmanagement.insert_position'),
                name: "item." + myId + ".position",
                store: [["beginning",t("wl_tagmanagement.beginning")],["end",t("wl_tagmanagement.end")]],
                triggerAction: "all",
                typeAhead: false,
                editable: false,
                forceSelection: true,
                mode: "local",
                value: data.position,
                width: 250
            },{
                xtype: "checkbox",
                fieldLabel: t("wl_tagmanagement.temporarily_disabled"),
                name: "item." + myId + ".disabled",
                checked: data.disabled
            },{
                xtype: "checkbox",
                fieldLabel: t("wl_tagmanagement.enabled_in_editmode"),
                name: "item." + myId + ".enabledInEditmode",
                checked: data.enabledInEditmode
            },{
                xtype: "datefield",
                name: "item." + myId + ".date",
                width: 220,
                style: "float: left; margin-right:5px;",
                fieldLabel: t("wl_tagmanagement.expiry"),
                value: data.date,
                format: "m/d/y",
                renderer: function(d) {
                    if(d instanceof Date) {
                        return Ext.Date.format(d, "m/d/y");
                    }
                    return null;
                }
            },{
                xtype: "timefield",
                name: "item." + myId + ".time",
                width: 120,
                style: "float: left;",
                value: data.date,
                format: 'H:i A',
                renderer: function(d) {
                    if(d instanceof Date) {
                        return Ext.Date.format(d, "H:i A");
                    }
                    return null;
                }
            }]
        });

        this.itemContainer.add(item);
        this.itemContainer.updateLayout();

        this.currentIndex++;
    },

    save: function () {

        var form = this.panel.getForm();
        var requestedName = form.findField("name").getValue();

        // Mirror the backend naming rules client-side so invalid renames fail before the request.
        if (!this.isValidTagName(requestedName)) {
            Ext.Msg.alert(' ', t('wl_tagmanagement.failed_to_create_new_item'));
            return;
        }

        if (this.isDuplicateTagName(requestedName)) {
            Ext.Msg.alert(' ', t('wl_tagmanagement.name_already_in_use'));
            return;
        }

        var payload = {
            name: requestedName,
            description: form.findField("description").getValue(),
            disabled: form.findField("disabled").getValue(),
            siteId: form.findField("siteId").getValue(),
            urlPattern: form.findField("urlPattern").getValue(),
            textPattern: form.findField("textPattern").getValue(),
            httpMethod: form.findField("httpMethod").getValue(),
            items: [],
            params: []
        };

        // Params are persisted as a dynamic list; there is intentionally no fixed 5-slot limit anymore.
        this.paramContainer.items.each(function (paramPanel) {
            if (!paramPanel.tagParamId) {
                return;
            }

            var prefix = "param." + paramPanel.tagParamId + ".";
            var nameField = form.findField(prefix + "name");
            var valueField = form.findField(prefix + "value");
            var paramName = nameField ? nameField.getValue() : "";

            if (paramName) {
                payload.params.push({
                    name: paramName,
                    value: valueField ? valueField.getValue() : ""
                });
            }
        });

        // Items: build structured array from item panels
        this.itemContainer.items.each(function (itemPanel) {
            if (!itemPanel.tagItemId) {
                return;
            }

            var prefix = "item." + itemPanel.tagItemId + ".";
            var dateField = form.findField(prefix + "date");
            var timeField = form.findField(prefix + "time");
            var dateValue = dateField ? dateField.getValue() : null;
            var timeValue = timeField ? timeField.getValue() : null;
            var timestamp = null;

            // Store expiry as ISO-8601 UTC (Zulu, trailing "Z") to avoid timezone ambiguity.
            if (dateValue instanceof Date && timeValue instanceof Date) {
                var combined = new Date(dateValue.getTime());
                combined.setHours(timeValue.getHours());
                combined.setMinutes(timeValue.getMinutes());
                combined.setSeconds(timeValue.getSeconds());
                combined.setMilliseconds(0);
                timestamp = combined.toISOString();
            }

            payload.items.push({
                code: form.findField(prefix + "code").getValue(),
                element: form.findField(prefix + "element").getValue(),
                position: form.findField(prefix + "position").getValue(),
                disabled: form.findField(prefix + "disabled").getValue(),
                enabledInEditmode: form.findField(prefix + "enabledInEditmode").getValue(),
                date: timestamp
            });
        });

        Ext.Ajax.request({
            url: Routing.generate('weblizards_tagmanagement_update'),
            method: "PUT",
            params: {
                configuration: Ext.encode(payload),
                name: this.data.name
            },
            success: this.saveOnComplete.bind(this)
        });
    },

    saveOnComplete: function (response) {
        var result = Ext.decode(response.responseText);
        if (!result || !result.success) {
            Ext.Msg.alert(' ', (result && result.error) ? result.error : t("wl_tagmanagement.failed_to_create_new_item"));
            return;
        }

        var newName = result.id || this.panel.getForm().findField("name").getValue();
        var oldName = this.data.name;

        this.data.name = newName;
        this.parentPanel.tree.getStore().load();

        if (oldName !== newName) {
            // Re-open the tab under the new key so panel ids stay aligned with the renamed config.
            this.parentPanel.getEditPanel().remove(this.panel);
            this.parentPanel.openTag(newName);
        }

        pimcore.helpers.showNotification(t("wl_tagmanagement.success"), t("wl_tagmanagement.saved_successfully"), "success");
    },

    isValidTagName: function (value) {
        value = Ext.String.trim(value || "");

        return value.length > 0 && /^[a-zA-Z0-9_-]+$/.test(value);
    },

    isDuplicateTagName: function (value) {
        value = Ext.String.trim(value || "");
        if (value === this.data.name) {
            return false;
        }

        var tags = this.parentPanel.tree.getRootNode().childNodes;
        for (var i = 0; i < tags.length; i++) {
            if (tags[i].text === value) {
                return true;
            }
        }

        return false;
    },

    getCurrentIndex: function () {
        return this.currentIndex;
    }

});
