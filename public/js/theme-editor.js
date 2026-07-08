/*
 * Combat UI OpenDXP Bundle
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * @copyright Copyright (c) 2026 Combat Jongerenmarketing en -communicatie B.V. (https://www.combat.nl)
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

opendxp.registerNS("opendxp.bundle.combatui.themeEditor");

/**
 * Theme editor tab: one collapsible fieldset per token group, a text field per token showing the
 * framework default as placeholder, and a light/dark field pair for color tokens. Only fields
 * with a value are stored as overrides; the backend turns them into the theme stylesheet.
 *
 * @private
 */
opendxp.bundle.combatui.themeEditor = Class.create({

    initialize: function () {
        Ext.Ajax.request({
            url: Routing.generate("combat_ui_admin_theme_data"),
            method: "GET",
            success: function (response) {
                this.data = Ext.decode(response.responseText);
                this.getTabPanel();
            }.bind(this),
            failure: function () {
                opendxp.helpers.showNotification(t("error"), t("combat_ui_theme_load_error"), "error");
            },
        });
    },

    activate: function () {
        Ext.getCmp("opendxp_panel_tabs").setActiveItem("combat_ui_theme_editor");
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.fields = [];

            this.formPanel = new Ext.form.Panel({
                bodyStyle: "padding: 20px",
                scrollable: true,
                border: false,
                fieldDefaults: {
                    labelWidth: 280,
                    msgTarget: "none",
                },
                items: this.data.groups.map(this.buildGroup.bind(this)),
            });

            this.panel = new Ext.Panel({
                id: "combat_ui_theme_editor",
                title: t("combat_ui_theme"),
                iconCls: "combat_ui_icon_theme",
                border: false,
                layout: "fit",
                closable: true,
                items: [this.formPanel],
                tbar: [
                    {
                        text: t("save"),
                        iconCls: "opendxp_icon_apply",
                        handler: this.save.bind(this),
                    },
                    "->",
                    {
                        text: t("combat_ui_theme_clear_all"),
                        iconCls: "opendxp_icon_delete",
                        handler: this.clearAll.bind(this),
                    },
                ],
            });

            const tabPanel = Ext.getCmp("opendxp_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("combat_ui_theme_editor");

            this.panel.on("destroy", function () {
                opendxp.globalmanager.remove("bundle_combatui_theme");
            });

            opendxp.layout.refresh();
        }

        return this.panel;
    },

    buildGroup: function (group) {
        const items = [];

        if (group.summary) {
            items.push({
                xtype: "component",
                cls: "combat_ui_theme_group_summary",
                html: Ext.util.Format.htmlEncode(group.summary),
            });
        }

        group.tokens.forEach(function (token) {
            items.push(group.kind === "color" ? this.buildColorRow(token) : this.buildValueRow(token));
        }.bind(this));

        return {
            xtype: "fieldset",
            title: group.label,
            collapsible: true,
            defaults: {
                anchor: "100%",
            },
            items: items,
        };
    },

    buildValueRow: function (token) {
        const override = this.data.overrides[token.name] || {};

        const field = new Ext.form.field.Text({
            fieldLabel: token.name,
            labelSeparator: "",
            anchor: "100%",
            emptyText: token.value,
            value: override.value || "",
        });
        this.fields.push({ token: token.name, slot: "value", field: field });

        return {
            xtype: "container",
            items: [field, this.buildDescription(token)],
        };
    },

    buildColorRow: function (token) {
        const override = this.data.overrides[token.name] || {};

        const lightField = this.buildColorField(token.light, override.light, t("combat_ui_theme_light"), "0 8 0 0");
        const darkField = this.buildColorField(token.dark, override.dark, t("combat_ui_theme_dark"), "0");
        this.fields.push({ token: token.name, slot: "light", field: lightField });
        this.fields.push({ token: token.name, slot: "dark", field: darkField });

        return {
            xtype: "container",
            items: [
                {
                    xtype: "fieldcontainer",
                    fieldLabel: token.name,
                    labelSeparator: "",
                    layout: "hbox",
                    items: [lightField, darkField],
                },
                this.buildDescription(token),
            ],
        };
    },

    buildColorField: function (defaultValue, currentValue, slotLabel, margin) {
        const field = new Ext.form.field.Text({
            flex: 1,
            margin: margin,
            emptyText: slotLabel + (defaultValue ? ": " + defaultValue : ""),
            value: currentValue || "",
            cuiDefaultValue: defaultValue || "",
            triggers: {
                swatch: {
                    cls: "combat_ui_color_swatch",
                },
            },
            listeners: {
                change: this.updateSwatch.bind(this),
                afterrender: this.updateSwatch.bind(this),
            },
        });

        return field;
    },

    updateSwatch: function (field) {
        if (!field.rendered) {
            return;
        }

        const trigger = field.getTrigger("swatch");
        if (!trigger || !trigger.el) {
            return;
        }

        // Invalid values simply leave the swatch untouched; the browser resolves anything valid,
        // including oklch() and var()-free clamp-less color syntax.
        trigger.el.setStyle("background-color", "transparent");
        const value = String(field.getValue() || "").trim() || field.cuiDefaultValue;
        if (value) {
            trigger.el.setStyle("background-color", value);
        }
    },

    buildDescription: function (token) {
        return {
            xtype: "component",
            cls: "combat_ui_theme_token_desc",
            html: Ext.util.Format.htmlEncode(token.description || ""),
        };
    },

    save: function () {
        const tokens = {};
        this.fields.forEach(function (entry) {
            const value = String(entry.field.getValue() || "").trim();
            if (value === "") {
                return;
            }
            if (!tokens[entry.token]) {
                tokens[entry.token] = {};
            }
            tokens[entry.token][entry.slot] = value;
        });

        Ext.Ajax.request({
            url: Routing.generate("combat_ui_admin_theme_save"),
            method: "PUT",
            jsonData: { tokens: tokens },
            success: function (response) {
                const data = Ext.decode(response.responseText);
                if (data && data.success) {
                    opendxp.helpers.showNotification(t("success"), t("combat_ui_theme_saved"), "success");
                } else {
                    opendxp.helpers.showNotification(t("error"), (data && data.message) || t("error"), "error");
                }
            },
            failure: function (response) {
                let message = t("error");
                try {
                    message = Ext.decode(response.responseText).message || message;
                } catch (e) {
                    // keep the generic message
                }
                opendxp.helpers.showNotification(t("error"), message, "error");
            },
        });
    },

    clearAll: function () {
        Ext.MessageBox.confirm(
            t("combat_ui_theme_clear_all"),
            t("combat_ui_theme_clear_all_confirm"),
            function (buttonValue) {
                if (buttonValue !== "yes") {
                    return;
                }
                this.fields.forEach(function (entry) {
                    entry.field.setValue("");
                });
            }.bind(this)
        );
    },
});
