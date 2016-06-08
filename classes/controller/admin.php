<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Foolz\FoolFrame\Model\Notices;

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
                'help' => _i('Up to 30 items. Remember to include both http and https URIs'),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'paragraph' => [
                'type' => 'paragraph',
                'help' => _i('OR:')
            ],
            'foolfuuka.plugin.cloudflare_cache_purge.purge_all' => [
                'type' => 'checkbox',
                'label' => _i(''),
                'help' => _i('Purge everything. Caution advised. It will take some time for the cache to be fully effective again')
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
            $notice = $this->purge_service->process($this->getPost('foolfuuka,plugin,cloudflare_cache_purge,uris'),$this->getPost('foolfuuka,plugin,cloudflare_cache_purge,purge_all'));
            $this->notices->set($notice[0],$notice[1]);
        }

        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
