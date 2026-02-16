# Pimcore Database Explorer

A Pimcore bundle that adds [Adminer](https://www.adminer.org/) as a database administration tool. Use it from **Tools → System Info & Tools → Database Administration** in the Pimcore admin.

Supports **Pimcore 11** and **Pimcore 12**.

## Features

- Full Adminer UI (tables, SQL, export, etc.) using your Pimcore database credentials
- Single sign-on: no separate Adminer login when logged into Pimcore admin
- Optional integration with **Pimcore Admin UI Classic** (menu entry under System Info & Tools)
- Optional **Pimcore Studio** plugin so the database explorer is available in the Studio UI
- UI enhancements: floating table header, timestamp display, SQL suggestions, table filter

## Requirements

- Pimcore ^11.0 or ^12.0
- PHP 8.1+
- [vrana/adminer](https://github.com/vrana/adminer) (installed automatically by the bundle)

## Installation

1. Install the bundle:

   ```bash
   composer require rohit-raj-verma/pimcore-database-explorer
   ```

2. Register the bundle in `config/bundles.php`:

   ```php
   return [
       // ...
       PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle\DatabaseExplorerBundle::class => ['all' => true],
   ];
   ```

3. Install assets:

   ```bash
   bin/console assets:install --symlink
   ```

4. In Pimcore admin go to **Tools → System Info & Tools → Database Administration** to open the database explorer.

## Configuration

The bundle uses your existing Pimcore database connection. No extra configuration is required.

CSRF protection is disabled for the database explorer route so Adminer can work correctly.

## Pimcore Admin UI Classic

If you use `pimcore/admin-ui-classic-bundle`, the bundle registers a **Database Administration** item under **Tools → System Info & Tools**. No version constraint is enforced; use any compatible version of the admin UI classic bundle.

## Pimcore Studio

If you use [Pimcore Studio UI](https://github.com/pimcore/studio-ui-bundle), the bundle can register a Studio plugin so the database explorer is available in the Studio interface. Use any compatible version of the Studio UI bundle.

### Build Studio assets

1. Go to the bundle’s `assets` directory and install dependencies:

   ```bash
   cd vendor/rohit-raj-verma/pimcore-database-explorer/assets
   npm install
   ```

2. Build the Studio bundle (output goes to `src/Resources/public/build/`):

   ```bash
   npm run build
   ```

3. Reinstall bundle assets in your Pimcore project if needed:

   ```bash
   bin/console assets:install --symlink
   ```

For development you can run `npm run dev` in the `assets` directory for a watch build.

## Repository

- **Git:** `git@github.com:rohit-raj-verma/pimcore-database-explorer.git`
- **GitHub:** https://github.com/rohit-raj-verma/pimcore-database-explorer

## License

MIT. See [LICENSE](LICENSE) or [LICENSE.md](LICENSE.md) in this repository.
