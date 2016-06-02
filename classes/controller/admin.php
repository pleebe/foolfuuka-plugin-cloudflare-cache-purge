<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareCachePurge extends \Foolz\FoolFrame\Controller\Admin
{
    protected $purge_service;

    public function before()
    {
        parent::before();

        $this->purge_service = $this->getContext()->getService('foolfuuka-plugin.cloudflare_cache_purge');
        $this->param_manager->setParam('controller_title', 'Cloudflare Cache Purge');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.mod');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open',
            ],
            'foolfuuka.plugin.cloudflare_cache_purge.uris' => [
                'type' => 'textarea',
                'label' => _i('URIs to purge from Cloudflare cache (one per line)'),
                'help' => _i('Up to 30 items'),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'separator-2' => [
                'type' => 'separator-short'
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ],
            'close' => [
                'type' => 'close'
            ],
        ];
    }

    function action_manage()
    {
        $this->param_manager->setParam('method_title', 'Manage');

        $data['form'] = $this->structure();

        if ($this->getPost()) {
            $this->purge_service->process($this->getPost('foolfuuka,plugin,cloudflare_cache_purge,uris'));
        }
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
