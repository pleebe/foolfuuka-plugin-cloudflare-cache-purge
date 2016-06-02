<?php

use Foolz\FoolFrame\Model\Autoloader;
use Foolz\FoolFrame\Model\Context;
use Foolz\Plugin\Event;

class HHVM_CloudflareCachePurge
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-cloudflare-cache-purge')
            ->setCall(function ($result) {
                /* @var Context $context */
                $context = $result->getParam('context');
                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');

                $autoloader->addClassMap([
                    'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareAdmin' => __DIR__ . '/classes/controller/cfadmin.php',
                    'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareCachePurge' => __DIR__ . '/classes/controller/admin.php',
                    'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareCacheNuke' => __DIR__ . '/classes/controller/nuke.php',
                    'Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model\CloudflareCachePurge' => __DIR__ . '/classes/model/purge.php',
                    'Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model\CloudflareCacheNuke' => __DIR__ . '/classes/model/nuke.php'
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.cloudflare_cache_purge', 'Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model\CloudflareCachePurge')
                    ->addArgument($context);
                $context->getContainer()
                    ->register('foolfuuka-plugin.cloudflare_full_purge', 'Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model\CloudflareCacheNuke')
                    ->addArgument($context);

                Event::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.afterAuth')
                    ->setCall(function ($result) use ($context) {
                        // don't add the admin panels if the user is not an admin
                        if ($context->getService('auth')->hasAccess('maccess.admin')) {
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.cloudflare_cfapi.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugin/cloudflare_cfadmin/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareAdmin::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.cloudflare_cache_purge.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugin/cloudflare_cache_purge/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareCachePurge::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.cloudflare_full_purge.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugin/cloudflare_full_purge/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\CloudflareCacheNuke::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );

                            Event::forge('Foolz\FoolFrame\Controller\Admin::before#var.sidebar')
                                ->setCall(function ($result) {
                                    $sidebar = $result->getParam('sidebar');
                                    $sidebar[]['plugin'] = [
                                        'name' => _i('Cloudflare'),
                                        'default' => 'manage',
                                        'position' => [
                                            'beforeafter' => 'before',
                                            'element' => 'account'
                                        ],
                                        'level' => 'admin',
                                        'content' => [
                                            'cloudflare_cfadmin/manage' => ['level' => 'admin', 'name' => 'Cloudflare API Settings', 'icon' => 'icon-leaf'],
                                            'cloudflare_cache_purge/manage' => ['level' => 'admin', 'name' => 'Cloudflare Cache Purge', 'icon' => 'icon-leaf'],
                                            'cloudflare_full_purge/manage' => ['level' => 'admin', 'name' => 'Cloudflare Full Cache Nuke', 'icon' => 'icon-leaf']
                                        ]
                                    ];
                                    $result->setParam('sidebar', $sidebar);
                                });
                        }
                    });
                Event::forge('Foolz\FoolFuuka\Model\Media::delete#call.beforeMethod')
                    ->setCall(function ($result) use ($context) {
                        $context->getService('foolfuuka-plugin.cloudflare_cache_purge')->beforeDeleteMedia($result);
                    });
            });
    }
}

(new HHVM_CloudflareCachePurge())->run();
