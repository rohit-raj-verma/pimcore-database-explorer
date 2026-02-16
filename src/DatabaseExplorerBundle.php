<?php

declare(strict_types=1);

namespace PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class DatabaseExplorerBundle extends AbstractPimcoreBundle
{
    public function getNiceName(): string
    {
        return 'Pimcore Database Explorer';
    }
}
