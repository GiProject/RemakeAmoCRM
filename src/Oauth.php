<?php

namespace lib\AmoCRM;


class Oauth
{
    
    public static function get($subdomain, $widget){
        
        $auth = file_get_contents( __DIR__ . '/autorization/' . $subdomain . '_' . $widget );
        
        if (time() > strtotime($auth['expires_in'])) {//требуется обновить токен
            $auth = self::refresh_token($auth, $widget);
        }
        
        return $auth;
        
    }
    
    public static function refresh_token($auth, $widget){

        if( !file_get_contents( __DIR__ . '/autorization/' . $auth['subdomain'] . '_' . $widget ) ) {
            return false;
        }
    
        $request_data = [
            'client_id' => $auth['client_id'],
            'client_secret' => $auth['client_secret'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $auth['refresh_token'],
            'redirect_uri' => 'https://pinschertest.ru/oauth?widget=' . $widget
        ];
    
        $client = new \GuzzleHttp\Client([
            'allow_redirects' => true
        ]);
    
        try {
            $response = $client->request('POST', 'https://' .  $auth['subdomain'] . '.amocrm.ru/oauth2/access_token',
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($request_data)
                ]
            );
            $result = json_decode($response->getBody()->getContents(), 1);
        } catch (RequestException $e) {
            print_r($e->getMessage());
            return;
        }
    
        if( !empty($result) ){
            $auth_data = [
                'subdomain' => $auth['subdomain'],
                'widget' => $_GET['widget'],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token']
                ( new \DateTime('now'))->modify('+ ' . $result['expires_in'] . ' seconds')->format('Y-m-d H:i:s')
            ];
            if( file_put_contents( __DIR__ . '/autorization/' . $auth['subdomain'] . '_' . $_GET['widget'], json_encode($auth_data) ) ){
                $amo = new \lib\AmoCRM\Client($auth['subdomain'], $_GET['widget']);
                $account = $amo->account->apiCurrent();
                $auth_data['account_id'] = $account['id'];
                file_put_contents(__DIR__ . '/autorization/' . $auth['subdomain'] . '_' . $_GET['widget'], json_encode($auth_data) );
            }
        }
    
        return $result;
    }
    
    public function install( $client_id, $client_secret, $oauth_url){
        
        if( !isset($_GET['code']) || !isset($_GET['referer']) || !isset($_GET['from_widget']) || !isset($_GET['widget']) ){
            return 'нет данных для авторизации';
        }
        
        $subdomain = substr($_POST['referer'], 0, strpos($_POST['referer'], '.'));
    
        if( !isset($_GET['widget']) ){
            return;
        }
    
        $request_data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'redirect_uri' => $oauth_url
        ];
        
        $url = 'https://' . $_GET['referer'] . '/oauth2/access_token/';
    
        $client = new \GuzzleHttp\Client([
            'allow_redirects' => true
        ]);
    
        try {
            $response = $client->request('POST', $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($request_data)
                ]
            );
        
            $result = json_decode($response->getBody()->getContents(), 1);
        } catch (RequestException $e) {
            echo print_r($e->getMessage(), 1) . "\n" . $url . "\n" . print_r($request_data, 1) . "\n";
            return;
        }
    
        if( !empty($result) ){
            $auth_data = [
                'subdomain' => $subdomain,
                'widget' => $_GET['widget'],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token']
                ( new \DateTime('now'))->modify('+ ' . $result['expires_in'] . ' seconds')->format('Y-m-d H:i:s')
            ];
            if( file_put_contents( __DIR__ . '/autorization/' . $subdomain . '_' . $_GET['widget'], json_encode($auth_data) ) ){
                $amo = new \lib\AmoCRM\Client($subdomain, $_GET['widget']);
                $account = $amo->account->apiCurrent();
                $auth_data['account_id'] = $account['id'];
                file_put_contents(__DIR__ . '/autorization/' . $subdomain . '_' . $_GET['widget'], json_encode($auth_data) );
            }
        }
    }
    
}