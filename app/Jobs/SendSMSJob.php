<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mobile;
    private $code;
    private $temp;
    private $token2;
    private $token3;

    /**
     * Create a new job instance.
     */
    public function __construct($mobile, $code, $temp, $token2 = null, $token3 = null)
    {
        $this->mobile = $mobile;
        $this->code = $code;
        $this->temp = $temp;
        $this->token2 = $token2;
        $this->token3 = $token3;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
//        DB::table('test')->insert(['name' => $this->mobile, 'res' => $this->code]);

        $client = new Client();
        $client->request('POST', 'https://api.kavenegar.com/v1/6D33665547746B37755A5A63334E7A345A30676B4F4953507565527546307A55/verify/lookup.json', [
            'form_params' => [
                'receptor' => $this->mobile,
                'token' => $this->code,
                'template' => $this->temp,
                'token2' => $this->token2,
                'token3' => $this->token3
            ]
        ]);
    }
}
