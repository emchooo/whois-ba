<?php

namespace App\Http\Controllers;

use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use Laravel\Lumen\Routing\Controller as BaseController;
use thiagoalessio\TesseractOCR\TesseractOCR;

class Controller extends BaseController
{

    protected $url = 'http://nic.ba/lat/menu/view/13';

    public function index($domain)
    {

        if( ! $this->validDomain($domain) ) {
            return 'Naziv domene nije validan';
        }

        $domain = $this->clearDomain($domain);

        $response = $this->makeRequest();

        $session_token = $this->getSessionToken($response);

        $hidden_input = $this->getHiddenInput($response);


        $client = new \GuzzleHttp\Client();
    	$response = $client->post($this->url, [
            'cookies'   => $this->cookieJar($session_token),
                'headers'   => [[
                    'Upgrade-Insecure-Requests' => 1,
                    'Host'  => 'nic.ba',
                    'Origin'    => 'http://nic.ba',
                    'Referer'   => $this->url,
                    'User-Agent'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
                ]]
            ,
            'form_params'   => [
                'whois_input' => $hidden_input,
                'whois_select_name' => $domain,
                'submit_check' => 1,
                'whois_select_type' => 1,
                'submit_check'  => 'on'
            ]
        ]);

        $image = $this->getImage($response);

        if(!$image) {
            return 'Ova domena nije registrovana';
        }

        $saved_image_name = $domain . '-' . date('Y-m-d-H-i-s') . '.png';
        $saved_image_path = 'images/' . $saved_image_name;

        copy($image, $saved_image_path);

        $text = (new TesseractOCR($saved_image_path))->lang('bos')->run();

        return $text;

    }

    protected function makeRequest()
    {
        $request = new \GuzzleHttp\Client(); 
        $response = $request->get($this->url);
        return $response;

    }

    protected function getImage($response)
    {
        $full_return = $response->getBody()->getContents();

        $crawler2 = new Crawler($full_return);

        if($crawler2->filter('.textNormal img')->count() > 0) {
            $image = $crawler2->filter('.textNormal img')->attr('src');
            return $image;
        }
        return '';
        
    }

    protected function getSessionToken($response)
    {
        $headers = $response->getHeaders();

        $session_token_request = $headers['Set-Cookie'][0];
        $session_token = substr($session_token_request, 10, 26);
        return $session_token;
    }

    protected function getHiddenInput($response)
    {
        $body = $response->getBody()->getContents();

        $crawler = new Crawler($body);

        $hidden_input = $crawler->filter('form > input')->attr('value');
        
        return $hidden_input;
    }

    protected function cookieJar($session_token)
    {
        $cookie = new \GuzzleHttp\Cookie\SetCookie();
        $cookie->setName('PHPSESSID');
        $cookie->setValue($session_token);
        $cookie->setDomain('nic.ba');

        $cookieJar = new CookieJar(
            false,
            array(
                $cookie,
            )
        );
        return $cookieJar;
    }

    protected function validDomain($domain)
    {
        if(preg_match('/[a-zA-Z0-9-]+\.ba$/', $domain)) {
            return true;
        }
        return false;
    }

    protected function clearDomain($domain)
    {
        return str_replace('.ba','',$domain);
    }
}
