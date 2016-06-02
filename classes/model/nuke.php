<?php

namespace Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model;

use Foolz\FoolFrame\Model\Context;
use Foolz\FoolFrame\Model\Model;
use Foolz\FoolFrame\Model\Preferences;
use Foolz\FoolFrame\Model\Logger;

class CloudflareCacheNuke extends Model
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->logger = $context->getService('logger');

        $this->preferences = $context->getService('preferences');
    }

    public function process($input)
    {
        $data = '{"purge_everything":true}';

        $zoneid = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.zoneid');
        $email = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.email');
        $xauth = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.xauth');

        if($zoneid===null||$zoneid===''||$email===null||$email===''||$xauth===null||$xauth==='')
            return '';

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/'.$zoneid.'/purge_cache');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Auth-Email: $email","X-Auth-Key: $xauth",'Content-Type: application/json'));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        $temp = json_decode($result);
        if($temp->{"success"} === false)
            $this->logger->error('cfapi: '.$result);

        curl_close($ch);

        return $data;
    }
}
