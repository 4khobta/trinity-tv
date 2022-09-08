<?php

class TrinityTV
{
    private int $partner_id;
    private string $salt;
    private string $api_url = 'http://partners.trinity-tv.net/partners/user/';
    public array $errors = [];

    public function __construct(int $partner_id, string $salt)
    {
        $this->partner_id = $partner_id;
        $this->salt = $salt;
    }

    private function client(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }

    private function execute(string $method, array $params = []): ?array
    {
        $url = $this->api_url . $method;
        $params = array_merge([
            'requestid' => hexdec(uniqid()),
            'partnerid' => $this->partner_id,
        ], $params);

        $hash_params = $params;
        unset($hash_params['note']);

        array_walk($hash_params, function (&$val, $key) {
            if (in_array($key, ['firstname', 'lastname', 'middlename', 'address']))
                $val = urlencode($val);
        });

        $data = array_merge($params, ['hash' => md5(implode($hash_params) . $this->salt)]);
        $response = json_decode($this->client($url . '?' . http_build_query($data)), true);

        if ($response && isset($response['result'])) {
            if ($response['result'] == 'success')
                return $response;
            else
                $this->errors[] = $response['result'];

        } else {
            $this->errors[] = 'Connection error';
        }
        return null;
    }

    public function getUser(int $id): ?array
    {
        /*
         *  subscrstatusid: 0 - active, 768 - blocked
         */
        return $this->execute('subscriptioninfo', [
            'localid' => $id
        ]);
    }

    public function getDevices(int $id): ?array
    {
        return $this->execute('devicelist', [
            'localid' => $id
        ]);
    }

    public function create(int $id, int $tariff_id): ?array
    {
        return $this->execute('create', [
            'localid' => $id,
            'subscrid' => $tariff_id
        ]);
    }

    public function addDeviceByMac(int $id, string $mac = null, string $note = null, string $uuid = null): ?array
    {
        return $this->execute($note ? 'autorizedevice_note' : 'autorizedevice', [
            'localid' => $id,
            'mac' => $mac,
            'uuid' => $uuid,
            'note' => mb_convert_encoding($note, "Windows-1251", "UTF-8") // WTF trinity ?
        ]);
    }

    public function addDeviceByCode(int $id, int|string $code, string $note = null): ?array
    {
        return $this->execute('autorizebycode', [
            'localid' => $id,
            'code' => $code,
            'note' => $note
        ]);
    }

    public function deleteDevice(int $id, string $mac = null, string $uuid = null): ?array
    {
        return $this->execute('deletedevice', [
            'localid' => $id,
            'mac' => $mac,
            'uuid' => $uuid
        ]);
    }

    public function updateDeviceNote(int $device_id, string $note): ?array
    {
        return $this->execute('updatenotebydevice', [
            'deviceid' => $device_id,
            'note' => $note
        ]);
    }

    public function suspend(int $id): ?array
    {
        return $this->subscribe($id, 'suspend');
    }

    public function resume(int $id): ?array
    {
        return $this->subscribe($id, 'resume');
    }

    private function subscribe(int $id, string $operation): ?array
    {
        return $this->execute('subscription', [
            'localid' => $id,
            'operationid' => $operation
        ]);
    }

    public function usersList(): ?array
    {
        return $this->execute('subscriberlist');
    }

    public function updateUser(int $id, string $first_name = null, string $last_name = null, string $middle_name = null, string $address = null): ?array
    {
        return $this->execute('updateuser', [
            'localid' => $id,
            'firstname' => $first_name,
            'lastname' => $last_name,
            'middlename' => $middle_name,
            'address' => $address
        ]);
    }

    public function getReport(): ?array
    {
        return $this->execute('listreportupdated');
    }

    public function getDeviceList(): ?array
    {
        return $this->execute('fulldevicelist');
    }

    public function changeContract(int|string $id, int|string $new_id): ?array
    {
        return $this->execute('newcontract', [
            'localid' => $id,
            'newcontract' => $new_id
        ]);
    }

    public function getLastSessions(): ?array
    {
        return $this->execute('getsessionsdate');
    }

    public function getPlaylist(int $id): ?array
    {
        return $this->execute('getplaylist', [
            'localid' => $id
        ]);
    }

    public function deletePlaylist(int $id, string $playlist): ?array // same deleteDevice
    {
        return $this->deleteDevice($id, uuid: $playlist);
    }

    public function getLink(int $id, int $site_id = 1): ?array //Autorize in site site_id: 1 - trinity, 2 - sweet.tv
    {
        return $this->execute('siteaccesslink', [
            'localid' => $id,
            'siteid' => $site_id
        ]);
    }

    public function getDeviceType(int|null|string $type_id): string
    {
        /*
         * $type_id - return from devices['device_type']
         */
        $types = [
            0 => 'DT_Unknown',
            1 => 'DT_DIB_120',
            2 => 'DT_IPTV_Player',
            7 => 'DT_MAG200',
            8 => 'DT_MAG250_Micro',
            9 => 'DT_MAG250_Mini',
            10 => 'DT_Himedia_HD600A',
            11 => 'DT_Android_Player',
            12 => 'DT_STB_Emul',
            13 => 'DT_SmartTV',
            14 => 'DT_iNext',
            15 => 'DT_M3U',
            16 => 'DT_AndroidTV',
            17 => 'DT_IOS_Player',
            18 => 'DT_MacOS_Player',
            19 => 'DT_Kivi_TV',
            20 => 'DT_GX_STB',
            21 => 'DT_NOMI_TV',
            22 => 'DT_Web_Browser',
            23 => 'DT_ERGO_TV',
            24 => 'DT_AppleTV',
            25 => 'DT_Xbox'
        ];
        if (array_key_exists($type_id, $types))
            return $types[$type_id];
        return 'Unknown';
    }
}
