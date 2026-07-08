/*
 * Combat UI OpenDXP Bundle
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * @copyright Copyright (c) 2026 Combat Jongerenmarketing en -communicatie B.V. (https://www.combat.nl)
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

opendxp.registerNS("opendxp.bundle.combatui.startup");

/**
 * Adds the Combat UI theme editor to the Extras menu for users holding the
 * combat_ui_theme permission.
 *
 * @private
 */
opendxp.bundle.combatui.startup = Class.create({

    initialize: function () {
        document.addEventListener(opendxp.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        const menu = e.detail.menu;
        const user = opendxp.globalmanager.get("user");
        const perspectiveCfg = opendxp.globalmanager.get("perspective");

        if (menu.extras && user.isAllowed("combat_ui_theme") && perspectiveCfg.inToolbar("extras.combat_ui_theme")) {
            menu.extras.items.push({
                text: t("combat_ui_theme"),
                iconCls: "combat_ui_nav_icon_theme",
                priority: 60,
                itemId: "opendxp_menu_extras_combat_ui_theme",
                handler: this.openThemeEditor,
            });
        }
    },

    openThemeEditor: function () {
        try {
            opendxp.globalmanager.get("bundle_combatui_theme").activate();
        } catch (e) {
            opendxp.globalmanager.add("bundle_combatui_theme", new opendxp.bundle.combatui.themeEditor());
        }
    },
});

const opendxpBundleCombatUi = new opendxp.bundle.combatui.startup();
