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
 * framework default as placeholder, and a light/dark field pair with a color picker for color
 * tokens. A preview iframe beside the form renders a sample page and receives the current field
 * values as an injected stylesheet on every edit, so changes are visible before saving. Only
 * fields with a value are stored as overrides; the backend turns them into the theme stylesheet.
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
            this.previewTheme = "";
            this.schedulePreviewUpdate = Ext.Function.createBuffered(this.applyPreviewCss, 300, this);

            this.formPanel = new Ext.form.Panel({
                region: "west",
                width: 620,
                minWidth: 420,
                split: true,
                collapsible: true,
                header: false,
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
                layout: "border",
                closable: true,
                items: [this.formPanel, this.buildPreviewPanel()],
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

    //
    // PREVIEW PANE
    //

    buildPreviewPanel: function () {
        this.previewFrame = new Ext.Component({
            autoEl: {
                tag: "iframe",
                src: Routing.generate("combat_ui_admin_theme_preview"),
                frameborder: 0,
            },
            cls: "combat_ui_theme_preview_frame",
            listeners: {
                afterrender: function (cmp) {
                    cmp.el.dom.addEventListener("load", this.onPreviewLoad.bind(this));
                }.bind(this),
            },
        });

        return new Ext.Panel({
            region: "center",
            border: false,
            layout: "fit",
            header: false,
            items: [this.previewFrame],
            tbar: [
                {
                    xtype: "segmentedbutton",
                    allowDepress: false,
                    items: [
                        { text: t("combat_ui_theme_preview_auto"), value: "", pressed: true },
                        { text: t("combat_ui_theme_light"), value: "light" },
                        { text: t("combat_ui_theme_dark"), value: "dark" },
                    ],
                    listeners: {
                        change: function (button, value) {
                            this.previewTheme = value;
                            this.applyPreviewTheme();
                        }.bind(this),
                    },
                },
                "->",
                {
                    iconCls: "opendxp_icon_reload",
                    tooltip: t("combat_ui_theme_preview_reload"),
                    handler: function () {
                        const frame = this.previewFrame.el && this.previewFrame.el.dom;
                        if (frame) {
                            frame.src = Routing.generate("combat_ui_admin_theme_preview");
                        }
                    }.bind(this),
                },
            ],
        });
    },

    getPreviewDocument: function () {
        const frame = this.previewFrame && this.previewFrame.el && this.previewFrame.el.dom;
        if (!frame) {
            return null;
        }
        try {
            return frame.contentDocument;
        } catch (e) {
            return null;
        }
    },

    onPreviewLoad: function () {
        this.applyPreviewTheme();
        this.applyPreviewCss();

        // var()-based defaults only resolve against the preview document, so swatches rendered
        // before the iframe finished loading get another pass now.
        this.fields.forEach(function (entry) {
            if (entry.slot !== "value") {
                this.updateSwatch(entry.field);
            }
        }.bind(this));
    },

    applyPreviewTheme: function () {
        const doc = this.getPreviewDocument();
        if (!doc || !doc.documentElement) {
            return;
        }
        if (this.previewTheme) {
            doc.documentElement.dataset.theme = this.previewTheme;
        } else {
            delete doc.documentElement.dataset.theme;
        }
    },

    /**
     * Mirrors ThemeCssGenerator: light values into `:root` plus the explicit light pin, dark
     * values into the prefers-color-scheme media query plus the explicit dark pin.
     */
    buildPreviewCss: function () {
        const root = {};
        const light = {};
        const dark = {};

        this.fields.forEach(function (entry) {
            const value = String(entry.field.getValue() || "").trim();
            if (value === "") {
                return;
            }
            if (entry.slot === "value") {
                root[entry.token] = value;
            } else if (entry.slot === "light") {
                root[entry.token] = value;
                light[entry.token] = value;
            } else if (entry.slot === "dark") {
                dark[entry.token] = value;
            }
        });

        const rule = function (selector, declarations, indent) {
            indent = indent || "";
            const lines = Object.keys(declarations).map(function (name) {
                return indent + "  " + name + ": " + declarations[name] + ";";
            });
            return indent + selector + " {\n" + lines.join("\n") + "\n" + indent + "}\n";
        };

        let css = "";
        if (Object.keys(root).length) {
            css += rule(":root", root);
        }
        if (Object.keys(light).length) {
            css += rule(':where(:root[data-theme="light"], [data-theme="light"])', light);
        }
        if (Object.keys(dark).length) {
            css += "@media (prefers-color-scheme: dark) {\n"
                + rule(':root:not([data-theme="light"])', dark, "  ")
                + "}\n";
            css += rule(':where(:root[data-theme="dark"], [data-theme="dark"])', dark);
        }

        return css;
    },

    applyPreviewCss: function () {
        const doc = this.getPreviewDocument();
        if (!doc || !doc.head) {
            return;
        }

        let style = doc.getElementById("combat-ui-theme-editor-css");
        if (!style) {
            style = doc.createElement("style");
            style.id = "combat-ui-theme-editor-css";
            doc.head.appendChild(style);
        }
        style.textContent = this.buildPreviewCss();
    },

    //
    // FORM
    //

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
            listeners: {
                change: function () {
                    this.schedulePreviewUpdate();
                }.bind(this),
            },
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
                    handler: this.showColorPicker.bind(this),
                },
            },
            listeners: {
                change: function (changedField) {
                    this.updateSwatch(changedField);
                    this.schedulePreviewUpdate();
                }.bind(this),
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
        // including oklch(). var() references need the framework tokens and are resolved against
        // the preview document (another pass runs when the preview finishes loading).
        trigger.el.setStyle("background-color", "transparent");
        let value = String(field.getValue() || "").trim() || field.cuiDefaultValue;
        if (value.indexOf("var(") !== -1) {
            // An about:blank body (iframe not loaded yet) has no children and no tokens.
            const previewDoc = this.getPreviewDocument();
            const loaded = previewDoc && previewDoc.body && previewDoc.body.firstElementChild;
            value = loaded ? this.resolveColor(value) || "" : "";
        }
        if (value) {
            trigger.el.setStyle("background-color", value);
        }
    },

    //
    // COLOR PICKER
    //

    showColorPicker: function (field) {
        const current = this.resolveColor(String(field.getValue() || "").trim() || field.cuiDefaultValue);

        // Mirrors the popup Ext.ux.colorpick.Field uses to host its selector.
        const win = new Ext.window.Window({
            closeAction: "destroy",
            referenceHolder: true,
            minWidth: 540,
            minHeight: 200,
            layout: "fit",
            header: false,
            resizable: true,
            items: {
                xtype: "colorselector",
                reference: "selector",
                showPreviousColor: true,
                showOkCancelButtons: true,
            },
        });

        const selector = win.lookupReference("selector");

        if (current) {
            const parsed = Ext.ux.colorpick.ColorUtils.parseColor(current);
            if (parsed) {
                selector.setColor(parsed);
                selector.setPreviousColor(parsed);
            }
        }

        selector.on("ok", function (sel) {
            field.setValue(this.formatColor(sel.getColor()));
            win.close();
        }.bind(this));
        selector.on("cancel", function () {
            win.close();
        });

        win.showBy(field.getTrigger("swatch").el, "tr-br?");
    },

    /**
     * Resolves any CSS color the field may hold — hex, rgb(), oklch(), even var() chains — to an
     * "rgba(r, g, b, a)" string the ExtJS picker understands. var() references are resolved
     * against the preview document, which has the framework tokens loaded.
     */
    resolveColor: function (value) {
        if (!value) {
            return null;
        }

        let resolved = value;
        const doc = this.getPreviewDocument() || document;
        if (doc.body) {
            const probe = doc.createElement("div");
            probe.style.display = "none";
            probe.style.color = value;
            doc.body.appendChild(probe);
            const computed = (doc.defaultView || window).getComputedStyle(probe).color;
            probe.remove();
            if (computed) {
                resolved = computed;
            }
        }

        const canvas = document.createElement("canvas");
        canvas.width = canvas.height = 1;
        const ctx = canvas.getContext("2d");
        try {
            ctx.fillStyle = resolved;
            ctx.fillRect(0, 0, 1, 1);
            const rgba = ctx.getImageData(0, 0, 1, 1).data;
            return "rgba(" + rgba[0] + ", " + rgba[1] + ", " + rgba[2] + ", " + Math.round((rgba[3] / 255) * 100) / 100 + ")";
        } catch (e) {
            return null;
        }
    },

    formatColor: function (color) {
        const alpha = typeof color.a === "number" ? color.a : 1;
        if (alpha >= 1) {
            const hex = function (channel) {
                return ("0" + Math.round(channel).toString(16)).slice(-2);
            };
            return "#" + hex(color.r) + hex(color.g) + hex(color.b);
        }

        return "rgba(" + Math.round(color.r) + ", " + Math.round(color.g) + ", " + Math.round(color.b) + ", " + Math.round(alpha * 100) / 100 + ")";
    },

    buildDescription: function (token) {
        return {
            xtype: "component",
            cls: "combat_ui_theme_token_desc",
            html: Ext.util.Format.htmlEncode(token.description || ""),
        };
    },

    //
    // ACTIONS
    //

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
