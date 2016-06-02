<?php

namespace Foolz\FoolFuuka\Plugins\CloudflareCachePurge\Model;

use Foolz\FoolFrame\Model\Context;
use Foolz\FoolFrame\Model\Model;
use Foolz\FoolFrame\Model\Preferences;
use Foolz\FoolFrame\Model\Logger;

use Foolz\FoolFuuka\Model\Comment;
use Foolz\FoolFuuka\Model\CommentBulk;
use Foolz\FoolFuuka\Model\CommentFactory;
use Foolz\FoolFuuka\Model\Media;
use Foolz\FoolFuuka\Model\MediaFactory;

class CloudflareCachePurge extends Model
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var RadixCollection
     */
    protected $radix_coll;

    /**
     * @var MediaFactory
     */
    protected $media_factory;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->logger = $context->getService('logger');

        $this->dc = $context->getService('doctrine');
        $this->radix_coll = $context->getService('foolfuuka.radix_collection');
        $this->media_factory = $this->getContext()->getService('foolfuuka.media_factory');

        $this->preferences = $context->getService('preferences');
    }

    public function beforeDeleteMedia($result)
    {
        /** @var Media $post */
        $post = $result->getObject();
        $file = [];

        try {
            $file['image'] = $post->getDir(false, true, true);
        } catch (\Foolz\FoolFuuka\Model\MediaException $e) {

        }

        try {
            $post->op = 0;
            $dir['thumb-0'] = $post->getDir(true, true, true);
        } catch (\Foolz\FoolFuuka\Model\MediaException $e) {

        }

        try {
            $post->op = 1;
            $dir['thumb-1'] = $post->getDir(true, true, true);
        } catch (\Foolz\FoolFuuka\Model\MediaException $e) {

        }

        $purgelist = [];

        foreach ($file as $uri) {
            if (null === $uri) {
                continue;
            }

            array_push($purgelist, preg_replace('/https?/', 'http', $this->preferences->get('foolfuuka.boards.url')).$uri, preg_replace('/https?/', 'https', $this->preferences->get('foolfuuka.boards.url')).$uri);
        }
        foreach ($dir as $uu) {
            if (null === $uu) {
                continue;
            }
            array_push($purgelist, preg_replace('/https?/', 'http', $this->preferences->get('foolfuuka.boards.url')).$uu, preg_replace('/https?/', 'https', $this->preferences->get('foolfuuka.boards.url')).$uu);
        }


        $data =  json_encode(array(
            'files'=>$purgelist
        ));

        $zoneid = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.zoneid');
        $email = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.email');
        $xauth = $this->preferences->get('foolfuuka.plugins.cloudflare_cache_purge.xauth');

        if($zoneid===null||$zoneid===''||$email===null||$email===''||$xauth===null||$xauth==='')
            return;

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
            $this->logger->error($data.' cfapi response: '.$result);

        curl_close($ch);
    }

    public function process($input)
    {
        $uris = preg_split('/\r\n|\r|\n/', $input);
        $purgelist = [];

        foreach ($uris as $link) {
            array_push($purgelist, $link);
        }

        $data =  json_encode(array(
            'files'=>$purgelist
        ));

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
            $this->logger->error($data.' cfapi response: '.$result);

        curl_close($ch);

        return $data;
    }
}
