<?php

namespace Cih\Framework\Repositories\Sms;

use App\Models\Core\ContactMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RomtechGateway
{
    protected $client_id;
    protected $secret;
    protected $callback_url;
    protected $url;
    protected $token_url;
    protected $token;
    protected $from;
    protected $message_type;


    public function __construct()
    {
        $configs = config('sms.connections')['romtech'];
        $this->client_id = $configs['client_id'];
        $this->secret = $configs['secret'];
        $this->url = $configs['url'];
        $this->token_url = $configs['api_token_url'];
        $this->callback_url = $configs['callback_url'];
        $this->token = $this->getToken();
        $this->from = $configs['from'];
        $this->message_type = $configs['message_type'];
    }

    public function getToken()
    {
        return Cache::remember('romtech_sms_token', now()->addDay(), function () {
            $token = Http::post($this->token_url, [
                'client_id' => $this->client_id,
                'client_secret' => $this->secret,
                'grant_type' => 'client_credentials'
            ])->json();

            return $token['access_token'];
        });
    }

    public function sendMessage(object $contact_messages)
    {
        $data = [
            'to' => [$contact_messages->phone],
            'message' => $contact_messages->message,
            'callback' => $this->callback_url,
            'from' => $this->from,
            "api" => "v2"
        ];

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->withToken($this->token)
            ->post($this->url, $data)
            ->json();
    }

}
