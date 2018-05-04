<?php
/**
 * Name:    XlRequest
 * Author:  Adipati arya
 *           aryaadipati2@gmail.com
 * @adipati
 *
 * Added Awesomeness: Adipati arya
 *
 * Created:  11.10.2017
 *
 * Description:  Modified auth system based on Guzzle with extensive customization. This is basically what guzzle should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP5 or above
 *
 * @package		Xlrequest
 * @author		aryaadipati2@gmail.com
 * @link		http://sshcepat.com/xl
 * @filesource	https://github.com/adipatiarya/XLRequest
 */
require 'vendor/autoload.php';
use GuzzleHttp\Client;

class XlRequest {
	
	private $imei; 
	
	private $msisdn;
	
	private $client;
	
	private $header;
	
	private $session;
	
	private $date;
	
	public function __construct() {
		
		$this->client =new Client(['base_uri' => 'https://my.xl.co.id']); 
		
		$this->imei = '3920182791'; 
		
		$this->date = date('Ymdhis');
		
		$this->header = [
			'Host' => 'my.xl.co.id',
			'User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
			'Accept'=> 'application/json, text/plain, */*',
			'Accept-Language'=> 'en-US,en;q=0.5',
			'Accept-Encoding'=> 'gzip, deflate, br',
			'Referer: https://my.xl.co.id/pre/index1.html',
			'Content-Type'=> 'application/json',
			'Access-Control-Allow-Origin: True',
			'Content-Length: 955',
			'Cookie: TS01a72baf=017f69ee3cb8465536131770f47ba7b8853db7df73ec5a24b32945977792c4ebe9a200d1bb0ec7453bcdda667612b316037c101d2b2662f0b6c3ac0321e640046b3a8ed02c; TS01e70a0f=017f69ee3cb0925fbed42b67f9cabc96d404050d2d0ac64d7235549282584b9eb0ac0c4d4967f89a8540dcd6f0154b69e0e1a66f2568725d3c9cef3560cb79b4693b5e5916; connect.sid=s%3AXxL3eXl1BwsX2PuBpULaaoUJ1O9WNsxQ.oPtJNo52ZfA4yhKXt3crp9Ismq7JoR3ep%2Bmej9jwfZg',
			'DNT: 1',
			'Connection: keep-alive',

		];
	}
	public function login($msisdn, $passwd) {
		$this->msisdn = $msisdn;
		
		$payload = [
			'Header'=>null,
			'Body'=> [
				'Header'=>[
					'IMEI'=>$this->imei,
					'ReqID'=>$this->date,
				],
				'LoginV2Rq'=>[
					'msisdn'=>$msisdn,
					'pass'=>$passwd
				]
			]
		];
		try {
			$response = $this->client->post('/pre/LoginV2Rq',
				[
					'debug' => FALSE,
					'json' => $payload,
					'headers' => $this->header
				]
			);
			$body= $response->getBody();
			
			if (json_decode((string) $body)->responseCode !== '01') {
				$this->session = json_decode((string) $body)->sessionId; //dapatkan session id
			}
			
			else {
				return false; //jika login gagal 
			}
			
		}
		catch(Exception $e) {}
		
	}
	public function register($idService) {
		$payload = [
			'Header'=>null,
			'Body'=> [
				'HeaderRequest'=>[
					'applicationID'=>'3',
					'applicationSubID'=>'1',
					'touchpoint'=>'MYXL',
					'requestID'=>$this->date,
					'msisdn'=>$this->msisdn,
					'serviceID'=>$idService	
				],
				'opPurchase'=>[
					'msisdn'=>$this->msisdn,
					'serviceid'=>$idService,
				],
				'Header' => [
					'IMEI'=>$this->imei,
					'ReqID'=>$this->date
				]
			],
			'sessionId' => $this->session
		];
		try {
			$response = $this->client->post('/pre/opPurchase',[
					'debug' => FALSE,
					'json' => $payload,
					'headers' => $this->header
			]);
			$status = json_decode((string) $response->getBody());
			if (isset($status->responseCode))
				return $status;
			
			return $this->cek($idService);	
		}
		catch(Exception $e) {}
	}
	private function cek($idService) {
		$payload = [
            'type'=>'thankyou',
            'param'=>'service_id=&package_service_id='.$idService,
            'lang'=>'bahasa',
            'msisdn'=>$this->msisdn,
            'IMEI'=>$this->imei,
            'sessionId'=>$this->session,
            'staySigned'=>'False',
            'platform'=>'04',
            'ReqID'=>$this->date,
            "serviceId"=>'',
            'packageAmt'=>'',
            "reloadType"=>'',
            "reloadAmt"=>'',
            'packageRegUnreg'=>'',
            'onNetLogin'=>'NO',
            'appVersion'=>'3.5.2',
            'sourceName'=>'Firefox',
            'sourceVersion'=>''
        ];
		try {
			$response = $this->client->post('/pre/CMS',[
				'debug' => FALSE,
				'json' => $payload,
				'headers' => $this->header
			]);
			
			return json_decode((string) $response->getBody());
		} 
		catch(Exception $e) {}
	}
}
?>
