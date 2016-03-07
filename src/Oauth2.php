<?php

namespace Phapper;

use Phapper\Exception\RedditAuthenticationException;

class OAuth2 {
    private $accessToken;
    private $tokenType;
    private $expiration;
    private $scope;

    public $username;
    private $password;
    private $appId;
    private $appSecret;
    private $userAgent;
    private $apiEndpoint;

    public function __construct(Config $config) {
        $this->username = $config->username;
        $this->password = $config->password;
        $this->appId = $config->appId;
        $this->appSecret = $config->appSecret;
        $this->userAgent = $config->userAgent;
        $this->apiEndpoint = $config->basicEndpoint;
        $this->requestAccessToken();
    }

    public function getAccessToken() {
        if (!(isset($this->accessToken) && isset($this->tokenType) && time()<$this->expiration)) {
            $this->requestAccessToken();
        }

        return array(
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType
        );
    }

    private function requestAccessToken() {
        $url = "{$this->apiEndpoint}/api/v1/access_token";
        $params = array(
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password
        );

        $options[CURLOPT_USERAGENT] = $this->userAgent;
        $options[CURLOPT_USERPWD] = $this->appId.':'.$this->appSecret;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_CONNECTTIMEOUT] = 5;
        $options[CURLOPT_TIMEOUT] = 10;
        $options[CURLOPT_CUSTOMREQUEST] = 'POST';
        $options[CURLOPT_POSTFIELDS] = $params;

        $response = null;
        $got_token = false;
        while (!$got_token) {
            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $response_raw = curl_exec($ch);
            $response = json_decode($response_raw);
            curl_close($ch);

            if (isset($response->access_token)) {
                $got_token = true;
            }
            else {
                if (isset($response->error)) {
                    if ($response->error === "invalid_grant") {
                        throw new RedditAuthenticationException("Supplied reddit username/password are invalid or the threshold for invalid logins has been exceeded.", 1);
                    }
                    elseif ($response->error === 401) {
                        throw new RedditAuthenticationException("Supplied reddit app ID/secret are invalid.", 2);
                    }
                }
                else {
                    fwrite(STDERR, "WARNING: Request for reddit access token has failed. Check your connection.\n");
                    sleep(5);
                }
            }
        }

        $this->accessToken = $response->access_token;
        $this->tokenType = $response->token_type;
        $this->expiration = time()+$response->expires_in;
        $this->scope = $response->scope;
    }
}