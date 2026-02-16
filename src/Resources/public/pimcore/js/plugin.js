pimcore.registerNS("pimcore.plugin.databaseexplorer");

pimcore.plugin.databaseexplorer = Class.create({
    getClassName: function () {
        return "pimcore.plugin.databaseexplorer";
    },

    initialize: function () {
        if (typeof pimcore.events !== 'undefined') {
            Ext.Object.each(pimcore.events, function (eventName) {
                document.addEventListener(pimcore.events[eventName], (function (e) {
                    if (typeof this[eventName] === 'function') {
                        this[eventName].apply(this, Object.values(e.detail));
                    }
                }).bind(this));
            }.bind(this));
        } else {
            pimcore.plugin.broker.registerPlugin(this);
        }
    },

    pimcoreReady: function () {
        var user = pimcore.globalmanager.get("user");
        if (!user || !user.admin) {
            return;
        }
        var extrasMenu = pimcore.globalmanager.get("layout_toolbar").extrasMenu;
        if (!extrasMenu) {
            return;
        }
        var systemMenu = extrasMenu.queryById('pimcore_menu_extras_system_info');
        if (!systemMenu) {
            Ext.each(extrasMenu.items.items, function (item) {
                if (item.text === t("system_infos_and_tools")) {
                    systemMenu = item;
                    return false;
                }
            });
        }
        if (!systemMenu) {
            return;
        }
        var dbMenuItem = null;
        Ext.each(systemMenu.menu.items.items, function (item) {
            if (item.itemId === 'pimcore_menu_extras_system_info_database_administration' || item.text === t("database_administration")) {
                dbMenuItem = item;
                return false;
            }
        });
        var openDatabaseExplorer = function () {
            pimcore.helpers.openGenericIframeWindow(
                "databaseexplorer",
                "/admin/DatabaseExplorerBundle/adminer",
                "pimcore_icon_mysql",
                "Database Admin"
            );
        };
        if (dbMenuItem === null) {
            systemMenu.menu.add({
                text: t("database_administration"),
                iconCls: "pimcore_nav_icon_mysql",
                handler: openDatabaseExplorer
            });
        } else {
            dbMenuItem.setHandler(openDatabaseExplorer);
        }
    }
});

new pimcore.plugin.databaseexplorer();
