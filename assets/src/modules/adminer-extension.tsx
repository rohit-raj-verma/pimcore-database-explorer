import {type AbstractModule, container} from '@pimcore/studio-ui-bundle'
import {serviceIds} from '@pimcore/studio-ui-bundle/app'
import {type MainNavRegistry} from '@pimcore/studio-ui-bundle/modules/app'
import {type WidgetRegistry} from '@pimcore/studio-ui-bundle/modules/widget-manager'
import AdminerWidget from "../components/AdminerWidget";

export const DatabaseExplorerExtension: AbstractModule = {
    onInit: (): void => {
        const mainNavRegistryService = container.get<MainNavRegistry>(serviceIds.mainNavRegistry)

        mainNavRegistryService.registerMainNavItem({
            path: 'Adminer',
            icon: 'pimcore'
        })

        mainNavRegistryService.registerMainNavItem({
            path: 'System/Adminer',
            widgetConfig: {
                name: 'DB Explorer',
                id: 'database-explorer',
                component: 'database-explorer',
                config: {
                    icon: {
                        type: 'name',
                        value: 'pimcore'
                    }
                }
            }
        })

        const widgetRegistryService = container.get<WidgetRegistry>(serviceIds.widgetManager)
        widgetRegistryService.registerWidget({
            name: 'database-explorer',
            component: AdminerWidget
        })
    }
}
