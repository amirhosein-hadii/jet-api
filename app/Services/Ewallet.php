<?php


namespace App\Services;


use App\Models\Order;
use App\Models\OrderLog;

use Illuminate\Http\Request;

class Ewallet
{

    const base_url = 'http://37.32.15.7:8000/api/v1/agency/';
    const username = 'daapapp';
    const password = '858585';
    public function __construct()
    {
    }

    public function login()
    {
        try {
            $data_json = json_encode([
                'username'   => self::username,
                'password'   => self::password,
            ]);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, self::base_url . "auth/login");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

            $header = array(
                'Accept-Language: fa',
                'Content-Type: application/json',
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);

            if ($response === false) {
                throw new \Exception(curl_error($curl), curl_errno($curl));
            }
            curl_close($curl);

            $res = json_decode($response, true);
            dd($res);

            if (!isset($res['data']['access_token'])) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }
            return ['status' => 400, 'msg' => 'عملیات با موفقیت انجام شد.', 'token' => null];

        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage(), 'token' => null];
        }
    }

    public function createUser($cellphone, $person, $nationalId = null, $f_name = null, $l_name = null, $address = null)
    {
        try {
            $data_json = json_encode([
                'cellphone'   => $cellphone,
                'person'      => $person,
                'f_name'      => $nationalId,
                'l_name'      => $f_name,
                'address'     => $l_name,
                'national_id' => $address,
            ]);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, self::base_url . "user/create");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

            $header = array(
                'Accept-Language: fa',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);

            if ($response === false) {
                throw new \Exception(curl_error($curl), curl_errno($curl));
            }
            curl_close($curl);

            $res = json_decode($response, true);
            dd($res);

            if (!isset($res['data']['access_token'])) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }
            return ['status' => 400, 'msg' => 'عملیات با موفقیت انجام شد.', 'token' => null];

        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage(), 'token' => null];
        }
    }
}
