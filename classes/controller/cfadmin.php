<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareAdmin extends \Foolz\FoolFrame\Controller\Admin
{
    public function before()
    {
        parent::before();

        $this->param_manager->setParam('controller_title', 'Cloudflare API Settings');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open',
            ],
            'foolfuuka.plugins.cloudflare_cache_purge.zoneid' => [
                'preferences' => true,
                'type' => 'input',
                'label' => _i('Cloudflare assigned Zone ID'),
                'help' => _i('You need to obtain this from the Cloudlare API using the "zones" endpoint <a href="https://api.cloudflare.com/#zone-properties">instructions here</a>'),
                'class' => 'span3',
                'validation' => [new Trim()]
            ],
            'foolfuuka.plugins.cloudflare_cache_purge.email' => [
                'preferences' => true,
                'type' => 'input',
                'label' => _i('Your Cloudflare email'),
                'help' => _i(''),
                'class' => 'span3',
                'validation' => [new Trim()]
            ],
            'foolfuuka.plugins.cloudflare_cache_purge.xauth' => [
                'preferences' => true,
                'type' => 'input',
                'label' => _i('Cloudflare X-Auth token'),
                'help' => _i('This is available from your Cloudflare account settings under "API Key"'),
                'class' => 'span3',
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

        $this->preferences->submit_auto($this->getRequest(), $data['form'], $this->getPost());

        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
