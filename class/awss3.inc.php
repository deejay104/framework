<?php
class s3_core
{
	protected $bucket_name = "";
	protected $aws_region = "";
	protected $aws_hostname = "";
	protected $aws_access_key_id = "";
	protected $aws_secret_access_key = "";
	protected $aws_service_name = 's3';
	protected $name="";

	# Constructor	
	function __construct($name)
	{
		global $MyOpt;
		global $gl_uid;

		$this->bucket_name = $MyOpt["s3"]["bucket"];
		$this->aws_region = $MyOpt["s3"]["region"];
		$this->aws_hostname = $MyOpt["s3"]["hostname"];
		$this->aws_access_key_id = $MyOpt["s3"]["key"];
		$this->aws_secret_access_key = $MyOpt["s3"]["secret"];
		$this->aws_service_name = 's3';

		$this->name=$name;
	}


	function load()
	{
		// path of file in bucket
		// example : 'records.json', 'files/test.png'
		$file_name_path = $this->name;

		/**** other variables that are automatically set ****/
		// bucket host name
		$host_name = ($this->aws_hostname != "") ? $this->aws_hostname : $this->bucket_name . ".s3.".$this->aws_region.".amazonaws.com";

		// payload
		// no payload in this API
		$content = '';

		// UTC timestamp and date
		$timestamp = gmdate('Ymd\THis\Z');
		$date = gmdate('Ymd');


		/**** Task 1 : create canonical request for aws signature 4 ****/
		// HTTP request headers as key & value
		$request_headers = array();
		$request_headers['Host'] = $host_name;
		$request_headers['x-amz-date'] = $timestamp;
		$request_headers['x-amz-content-sha256'] = hash('sha256', $content);
		// sort it in ascending order
		ksort($request_headers);

		// canonical headers
		$canonical_headers = [];
		foreach($request_headers as $key => $value) {
			$canonical_headers[] = strtolower($key) . ":" . $value;
		}
		$canonical_headers = implode("\n", $canonical_headers);

		// signed headers
		$signed_headers = [];
		foreach($request_headers as $key => $value) {
			$signed_headers[] = strtolower($key);
		}
		$signed_headers = implode(";", $signed_headers);

		// cannonical request 
		$canonical_request = [];
		$canonical_request[] = "GET";
		$canonical_request[] = "/" . $file_name_path;
		$canonical_request[] = "";
		$canonical_request[] = $canonical_headers;
		$canonical_request[] = "";
		$canonical_request[] = $signed_headers;
		$canonical_request[] = hash('sha256', $content);
		$canonical_request = implode("\n", $canonical_request);
		$hashed_canonical_request = hash('sha256', $canonical_request);



		/**** Task 2 : creating a string to sign for aws signature 4 ****/
		// AWS scope
		$scope = [];
		$scope[] = $date;
		$scope[] = $this->aws_region;
		$scope[] = $this->aws_service_name;
		$scope[] = "aws4_request";

		// string to sign
		$string_to_sign = [];
		$string_to_sign[] = "AWS4-HMAC-SHA256"; 
		$string_to_sign[] = $timestamp; 
		$string_to_sign[] = implode('/', $scope);
		$string_to_sign[] = $hashed_canonical_request;
		$string_to_sign = implode("\n", $string_to_sign);



		/**** Task 3 : calculating signature for aws signature 4 ****/
		// signing key
		$kSecret = 'AWS4' . $this->aws_secret_access_key;
		$kDate = hash_hmac('sha256', $date, $kSecret, true);
		$kRegion = hash_hmac('sha256', $this->aws_region, $kDate, true);
		$kService = hash_hmac('sha256', $this->aws_service_name, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		// signature
		$signature = hash_hmac('sha256', $string_to_sign, $kSigning);



		/**** Task 4 : Add signature to HTTP request ****/
		// authorization
		$authorization = [
			'Credential=' . $this->aws_access_key_id . '/' . implode('/', $scope),
			'SignedHeaders=' . $signed_headers,
			'Signature=' . $signature
		];
		$authorization = 'AWS4-HMAC-SHA256' . ' ' . implode( ',', $authorization);



		/**** send HTTP request ****/
		// curl headers
		$curl_headers = [ 'Authorization: ' . $authorization ];
		foreach($request_headers as $key => $value) {
			$curl_headers[] = $key . ": " . $value;
		}

		$url = 'https://' . $host_name . '/' . $file_name_path;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $data = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code != 200) 
		{
			$data="";
		}	

		return $data;
	}

	function save($file,$type="image/png")
	{
		/// AWS API keys

		// Bucket
		

		// aws region
		// example : 'us-east-1', 'us-east-2' etc

		// path of file in bucket
		// example : 'records.json', 'files/test.png'
		$file_name_path = $this->name;


		/**** other variables that are automatically set ****/
		// bucket host name
		$host_name = ($this->aws_hostname != "") ? $this->aws_hostname : $this->bucket_name . ".s3.".$this->aws_region.".amazonaws.com";


		// service name for S3

		// payload
		$content = file_get_contents($file);

		// UTC timestamp and date
		$timestamp = gmdate('Ymd\THis\Z');
		$date = gmdate('Ymd');


		/**** Task 1 : create canonical request for aws signature 4 ****/
		// HTTP request headers as key & value
		$request_headers = array();
		$request_headers['Host'] = $host_name;
		$request_headers['x-amz-date'] = $timestamp;
		$request_headers['x-amz-content-sha256'] = hash('sha256', $content);
		// sort it in ascending order
		ksort($request_headers);

		// canonical headers
		$canonical_headers = [];
		foreach($request_headers as $key => $value) {
			$canonical_headers[] = strtolower($key) . ":" . $value;
		}
		$canonical_headers = implode("\n", $canonical_headers);

		// signed headers
		$signed_headers = [];
		foreach($request_headers as $key => $value) {
			$signed_headers[] = strtolower($key);
		}
		$signed_headers = implode(";", $signed_headers);

		// cannonical request 
		$canonical_request = [];
		$canonical_request[] = "PUT";
		$canonical_request[] = "/" . $file_name_path;
		$canonical_request[] = "";
		$canonical_request[] = $canonical_headers;
		$canonical_request[] = "";
		$canonical_request[] = $signed_headers;
		$canonical_request[] = hash('sha256', $content);
		$canonical_request = implode("\n", $canonical_request);
		$hashed_canonical_request = hash('sha256', $canonical_request);



		/**** Task 2 : creating a string to sign for aws signature 4 ****/
		// AWS scope
		$scope = [];
		$scope[] = $date;
		$scope[] = $this->aws_region;
		$scope[] = $this->aws_service_name;
		$scope[] = "aws4_request";

		// string to sign
		$string_to_sign = [];
		$string_to_sign[] = "AWS4-HMAC-SHA256"; 
		$string_to_sign[] = $timestamp; 
		$string_to_sign[] = implode('/', $scope);
		$string_to_sign[] = $hashed_canonical_request;
		$string_to_sign = implode("\n", $string_to_sign);



		/**** Task 3 : calculating signature for aws signature 4 ****/
		// signing key
		$kSecret = 'AWS4' . $this->aws_secret_access_key;
		$kDate = hash_hmac('sha256', $date, $kSecret, true);
		$kRegion = hash_hmac('sha256', $this->aws_region, $kDate, true);
		$kService = hash_hmac('sha256', $this->aws_service_name, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		// signature
		$signature = hash_hmac('sha256', $string_to_sign, $kSigning);



		/**** Task 4 : Add signature to HTTP request ****/
		// authorization
		$authorization = [
			'Credential=' . $this->aws_access_key_id . '/' . implode('/', $scope),
			'SignedHeaders=' . $signed_headers,
			'Signature=' . $signature
		];
		$authorization = 'AWS4-HMAC-SHA256' . ' ' . implode( ',', $authorization);



		/**** send HTTP request ****/
		// curl headers
		$curl_headers = [ 'Authorization: ' . $authorization ];
		foreach($request_headers as $key => $value) {
			$curl_headers[] = $key . ": " . $value;
		}

        $url = 'https://' . $host_name . '/' . $file_name_path;
		$ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		$data = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($http_code != 200) 
		{
			return false;
		}	

		return true;
	}

}
?>