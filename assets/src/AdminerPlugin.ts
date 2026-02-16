import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { DatabaseExplorerExtension } from "./modules/adminer-extension";

export const DatabaseExplorerStudioPlugin: IAbstractPlugin = {
    name: 'MainNavEntryPlugin',

    onStartup ({ moduleSystem }) {
        moduleSystem.registerModule(DatabaseExplorerExtension)
    }
}
