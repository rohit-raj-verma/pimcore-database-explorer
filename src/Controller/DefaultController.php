<?php

declare(strict_types=1);

namespace PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle\Controller {
    use PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle\Lib\Helper;
    use Pimcore\Helper\Mail as MailHelper;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Profiler\Profiler;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController
    {
        protected string $adminerHome = '';

        #[Route(path: '/admin/DatabaseExplorerBundle/adminer', name: 'database_explorer_adminer')]
        public function adminerAction(?Profiler $profiler): Response
        {
            $this->prepare();

            if ($profiler !== null) {
                $profiler->disable();
            }

            chdir($this->adminerHome . 'adminer');
            $baseUrl = Helper::getHostUrl() . '/admin/DatabaseExplorerBundle/adminer';
            ob_start(static function (string $html) use ($baseUrl) {
                try {
                    if (method_exists(MailHelper::class, 'setAbsolutePaths')) {
                        /** @psalm-suppress InternalMethod, InternalClass */
                        $html = MailHelper::setAbsolutePaths($html, null, $baseUrl);
                    } else {
                        throw new \Exception('Method setAbsolutePaths does not exist in MailHelper.');
                    }

                    return str_replace('static/editing.js', $baseUrl . '/static/editing.js', $html);
                } catch (\Exception $e) {
                    throw new \Exception('Error in MailHelper::setAbsolutePaths: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
                }
            });

            /** @psalm-suppress UnresolvableInclude */
            include $this->adminerHome . 'adminer/index.php';

            @ob_get_flush();

            $response = new Response();

            return $this->mergeAdminerHeaders($response);
        }

        #[Route(path: '/admin/DatabaseExplorerBundle/adminer/static/{path}', requirements: ['path' => '.*'])]
        #[Route(path: '/admin/DatabaseExplorerBundle/externals/{path}', requirements: ['path' => '.*'], defaults: ['type' => 'external'])]
        public function proxyAction(Request $request): Response
        {
            $this->prepare();

            $response = new Response();
            $content = '';

            $path = $request->get('path');

            if (preg_match('@\.(css|js|ico|png|jpg|gif)$@', (string) $path)) {
                if ('external' === $request->get('type')) {
                    $path = '../' . $path;
                }

                if (str_starts_with((string) $path, 'static/')) {
                    $path = 'adminer/' . $path;
                }

                $filePath = $this->adminerHome . '/' . $path;
                if (!file_exists($filePath)) {
                    $filePath = $this->adminerHome . 'adminer/static/' . $path;
                }
                if (preg_match('@.css$@', (string) $path)) {
                    $response->headers->set('Content-Type', 'text/css');
                } elseif (preg_match('@.js$@', (string) $path)) {
                    $response->headers->set('Content-Type', 'text/javascript');
                }

                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);

                    if (preg_match('@default.css$@', (string) $path)) {
                        $content .= file_get_contents($this->adminerHome . 'designs/konya/adminer.css');
                    }
                }
            }

            $response->setContent($content);

            return $this->mergeAdminerHeaders($response);
        }

        public function prepare(): void
        {
            /** @psalm-suppress UndefinedConstant */
            $this->adminerHome = PIMCORE_COMPOSER_PATH . '/vrana/adminer/';
        }

        protected function mergeAdminerHeaders(Response $response): Response
        {
            if (!headers_sent()) {
                $headersRaw = headers_list();

                foreach ($headersRaw as $header) {
                    $header = explode(':', $header, 2);
                    [$headerKey, $headerValue] = $header;

                    if ($headerKey && $headerValue) {
                        $response->headers->set($headerKey, $headerValue);
                    }
                }

                header_remove();
            }

            return $response;
        }
    }
}

namespace {
    use Pimcore\Cache;
    use Pimcore\Db;
    use Pimcore\Tool\Session;
    use PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle\Lib\AdminerPlugins;

    if (!function_exists('adminer_object')) {
        function adminer_object()
        {
            /** @psalm-suppress UndefinedConstant */
            $pluginDir = PIMCORE_COMPOSER_PATH . '/vrana/adminer/plugins';

            /** @psalm-suppress UnresolvableInclude */
            include_once $pluginDir . '/plugin.php';

            foreach (glob($pluginDir . '/*.php') ?: [] as $filename) {
                /** @psalm-suppress UnresolvableInclude */
                include_once $filename;
            }

            $plugins = [
                new AdminerPlugins(),
                new \AdminerFrames(),
                new \AdminerDumpDate(),
                new \AdminerDumpJson(),
                new \AdminerDumpBz2(),
                new \AdminerDumpZip(),
                new \AdminerDumpXml(),
                new \AdminerDumpAlter(),
            ];

            /** @psalm-suppress InternalMethod, InternalClass */
            $driverOptions = \Pimcore\Db::get()->getParams()['driverOptions'] ?? [];
            $ssl = [
                'key' => $driverOptions[\PDO::MYSQL_ATTR_SSL_KEY] ?? null,
                'cert' => $driverOptions[\PDO::MYSQL_ATTR_SSL_CERT] ?? null,
                'ca' => $driverOptions[\PDO::MYSQL_ATTR_SSL_CA] ?? null,
            ];
            if (null !== $ssl['key'] || null !== $ssl['cert'] || null !== $ssl['ca']) {
                $plugins[] = new \AdminerLoginSsl($ssl);
            }

            // Define the Adminer customization lazily to avoid loading Adminer classes during Symfony container build.
            if (!class_exists('AdminerPimcore', false)) {
                class AdminerPimcore extends \AdminerPlugin
                {
                    public function name(): string
                    {
                        return '';
                    }

                    public function loginForm(): void
                    {
                        parent::loginForm();
                        echo '<script' . nonce() . ">document.querySelector('input[name=auth\\\\[db\\\\]]').value='" . $this->database() . "'; document.querySelector('form').submit()</script>";
                    }

                    public function permanentLogin($create = false): string
                    {
                        if (method_exists(Session::class, 'getSessionId')) {
                            return Session::getSessionId();
                        }

                        return '';
                    }

                    public function login($login, $password): bool
                    {
                        return true;
                    }

                    public function credentials(): array
                    {
                        /** @psalm-suppress InternalMethod, InternalClass */
                        $params = \Pimcore\Db::get()->getParams();

                        $host = $params['host'] ?? null;
                        if ($port = $params['port'] ?? null) {
                            $host .= ':' . $port;
                        }

                        return [
                            $host,
                            $params['user'] ?? null,
                            $params['password'] ?? null,
                        ];
                    }

                    public function database(): string
                    {
                        $db = \Pimcore\Db::get();

                        return $db->getDatabase();
                    }

                    public function databases($flush = true)
                    {
                        $cacheKey = 'pimcore_adminer_databases';

                        if (!$return = Cache::load($cacheKey)) {
                            $return = Db::getConnection()->fetchAllAssociative('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA');

                            foreach ($return as &$ret) {
                                $ret = $ret['SCHEMA_NAME'];
                            }

                            Cache::save($return, $cacheKey);
                        }

                        return $return;
                    }
                }
            }

            return new \AdminerPimcore($plugins);
        }
    }
}
