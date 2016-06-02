<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareCacheNuke extends \Foolz\FoolFrame\Controller\Admin
{
    protected $purge_service;

    public function before()
    {
        parent::before();

        $this->purge_service = $this->getContext()->getService('foolfuuka-plugin.cloudflare_full_purge');
        $this->param_manager->setParam('controller_title', 'Cloudflare Full Cache Nuke ☢ CAUTION ☢ this function should be used sparingly ☢ CAUTION ☢');
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
            'label' => [
                'type' => 'label',
                'label' => _i('test'),
                'help' => _i('test'),
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Full Cache Purge')
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
            $this->purge_service->process($this->getPost('foolfuuka,plugin,cloudflare_full_purge,uris'));
        }
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
