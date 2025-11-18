<?php

defined('BASEPATH') or exit('No direct script access allowed');

class HMRC extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->library("Aauth");

        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->premission(1)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
    }

    public function auth()
    {
     redirect('https://test-www.tax.service.gov.uk/oauth/authorize?response_type=code&client_id=KxYwSNDont3Pq4hJT1GRutoCSovc&scope=write%3Avat%2Bread%3Avat&redirect_uri=https%3A%2F%2Fdemo.cloudbillingmanager.com%2Fv2.1%2FHMRC%2Fconnect');

    }

    public function connect()
    {
        $auth = $_GET['code'];

        $url = 'https://test-api.service.hmrc.gov.uk/oauth/token';
        $client_secret = '8d0138cd-e3d5-4480-8aa5-1233ddb61421';
        $client_id = 'KxYwSNDont3Pq4hJT1GRutoCSovc';
        $grant_type = 'authorization_code';
        $redirect_uri = 'https://demo.cloudbillingmanager.com/v2.1/HMRC/connect';
        $code = $auth;
        $code_verifier = 'https://test-api.service.hmrc.gov.uk/oauth/token'; 
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'client_secret' => $client_secret,
            'client_id' => $client_id,
            'grant_type' => $grant_type,
            'redirect_uri' => $redirect_uri,
            'code' => $code
          //  'code_verifier' => $code_verifier
        )));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $result = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($result, true);
        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];
        $expires_in = $response['expires_in'];
        $scope = $response['scope'];
        $token_type = $response['token_type'];

        $this->db->set('access_token', $access_token);
        $this->db->set('refresh_token', $refresh_token);
        $this->db->set('scope', $scope);
        $this->db->set('is_HMRC_connected', 1);
        $this->db->set('authorization_code', $auth);
        $this->db->where('id', $this->aauth->get_user()->id);
        $this->db->update('geopos_users');

        // $head['title'] = "Outstanding VAT";
        // $head['usernm'] = $this->aauth->get_user()->username;
        // $this->load->view('fixed/header', $head);
        // $this->load->view('transactions/vat_subimissions');
        // $this->load->view('fixed/footer');
 
       redirect('transactions/obligations');



    }

    public function check_vat()
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://test-api.service.hmrc.gov.uk/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_secret=[YOUR-CLIENT-SECRET]\n&client_id=[YOUR-CLIENT-ID]\n&grant_type=refresh_token\n&refresh_token=[REFRESH-TOKEN]");

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $vrn = "238848617"; // Replace with your VAT registration number
        $from = "2018-01-25"; // Replace with the desired 'from' date
        $to = "2018-12-31"; // Replace with the desired 'to' date
        $govTestScenario = "SINGLE_PAYMENT"; // Replace with the desired scenario or leave as null

       // $this->retrieveVATPayments($vat_token);
    }

private function generateUniqueDeviceIdentifier() {
    // Get relevant device information
    $userAgent = $_SERVER['HTTP_USER_AGENT']; // Get user agent
    $ipAddress = $_SERVER['REMOTE_ADDR']; // Get IP address
    
    // Concatenate device information
    $deviceInfo = $userAgent . $ipAddress;

    // Generate hash
    $uniqueIdentifier = sha1($deviceInfo);

    return $uniqueIdentifier;
}

private function generateUTCtimestamp() {
    // Create a DateTime object with the current time in UTC
    $dateTime = new DateTime('now', new DateTimeZone('UTC'));

    // Format the DateTime object as per the required format
    $timestamp = $dateTime->format('Y-m-d\TH:i:s.u\Z');

    return $timestamp;
}

private function getLocalTimezoneOffset() {
    // Get the local timezone of the originating device
    $timezone = new DateTimeZone(date_default_timezone_get());

    // Get the offset from UTC in seconds
    $offsetSeconds = $timezone->getOffset(new DateTime());

    // Convert the offset to hours and minutes
    $hours = floor(abs($offsetSeconds) / 3600);
    $minutes = floor((abs($offsetSeconds) - ($hours * 3600)) / 60);

    // Determine the sign of the offset
    $sign = ($offsetSeconds < 0) ? '-' : '+';

    // Format the offset
    $offsetFormatted = sprintf("UTC%s%02d:%02d", $sign, $hours, $minutes);

    return $offsetFormatted;
}


    public function hello_user(){
$token = $this->getRefreshToken();

 

        $deviceIdentifier =$this->generateUniqueDeviceIdentifier();
$utcTimestamp =  $this->generateUTCtimestamp();
$localTimezoneOffset =  $this->getLocalTimezoneOffset();
$user_email = 'yamin.virk@gmail.com'; // replace this with logged in user email
$req_by = '203.0.113.6'; //Server IP
$req_for = '198.51.100.0'; // Vendor IP
		$headers = [
		    'Accept: application/vnd.hmrc.1.0+json',
		    'Authorization: Bearer ' . $access_token,
             'Client-ID: KxYwSNDont3Pq4hJT1GRutoCSovc',
             'Client-Secret: 8d0138cd-e3d5-4480-8aa5-1233ddb61421',
             'Gov-*: Gov-Client-Connection-Method: WEB_APP_VIA_SERVER Gov-Client-Browser-JS-User-Agent:'.$_SERVER['HTTP_USER_AGENT'].' Gov-Client-Device-ID: '.$deviceIdentifier.' Gov-Client-Public-IP:'.$_SERVER['REMOTE_ADDR'].' Gov-Client-Timezone: '.$utcTimestamp.' Gov-Client-Public-Port:443 Gov-Client-Timezone:'.$localTimezoneOffset.' Gov-Client-User-IDs: my-application='.$user_email.'Gov-Vendor-Forwarded: by='.$_SERVER['REMOTE_ADDR'].'&for='.$req_for.' Gov-Vendor-License-IDs: my-licensed-software=8D7963490527D33716835EE7C195516D5E562E03B224E9B359836466EE40CDE1 Gov-Vendor-Product-Name: Cloud%20Billing%20Manager
 Gov-Vendor-Public-IP: '.$_SERVER['REMOTE_ADDR'].' Gov-Vendor-Version: my-web-app=2.1',
		];

	
	// Call the hello application API.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://test-api.service.hmrc.gov.uk/hello/application");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$server_output = curl_exec ($ch);
    if ($server_output === false) {
    echo "Curl error: " . curl_error($ch);
} else {
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code !== 200) {
        echo "API response error. HTTP Status Code: " . $http_code;
    } else {
        echo $server_output;
    }
}

	curl_close ($ch);

	// Print what we get.
	print  $server_output;

		// Print what the hello user API returns.
		//echo $server_output;
    }

    public function getRefreshToken(){

                $client_secret = '8d0138cd-e3d5-4480-8aa5-1233ddb61421';
                $client_id = 'KxYwSNDont3Pq4hJT1GRutoCSovc';
                $grant_type = 'refresh_token';
                $refresh_token = $this->aauth->get_user()->refresh_token;
                $url = 'https://test-api.service.hmrc.gov.uk/oauth/token';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'client_secret' => $client_secret,
                    'client_id' => $client_id,
                    'grant_type' => $grant_type,
                    'refresh_token' => $refresh_token
                )));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = json_decode($result, true);
                if ($response === null) {
                    echo "Error: Unable to decode JSON response";
                    exit;
                }
                $access_token = $response['access_token'];
                $refresh_token = $response['refresh_token'];
                $expires_in = $response['expires_in'];
                $scope = $response['scope'];
                $token_type = $response['token_type'];

                $this->db->set('access_token', $access_token);
                $this->db->set('refresh_token', $refresh_token);
                $this->db->set('scope', $scope);
                $this->db->where('id', $this->aauth->get_user()->id);
                $this->db->update('geopos_users');
            


                return $access_token;
              



    }


    public function retrieveVATPayments()
    {
        $token = $this->getRefreshToken();
        $vrn = "238848617"; 
        $from = "2024-01-10"; 
        $to = "2024-02-10";
        $govTestScenario = "SINGLE_PAYMENT"; 
        $govTestScenario = "QUARTERLY_NONE_MET";
        $curl = curl_init();
        $token = $this->getRefreshToken();

 

        $deviceIdentifier =$this->generateUniqueDeviceIdentifier();
        $utcTimestamp =  $this->generateUTCtimestamp();
        $localTimezoneOffset =  $this->getLocalTimezoneOffset();
        $user_email = 'yamin.virk@gmail.com'; // replace this with logged in user email
        $req_by = '203.0.113.6'; //Server IP
        $req_for = '198.51.100.0'; // Vendor IP
        $queryParams = http_build_query(array(
            'from' => $from,
            'to' => $to,
        ));

       // $url = "https://test-api.service.hmrc.gov.uk/organisations/vat/$vrn/payments?" . $queryParams;
        $url = "https://test-api.service.hmrc.gov.uk/organisations/vat/$vrn/obligations?" . $queryParams;
        $headers = array(
            "Accept: application/vnd.hmrc.1.0+json",
            "Authorization: Bearer " . $token,
            "Client-ID: KxYwSNDont3Pq4hJT1GRutoCSovc",
            'Client-Secret: 8d0138cd-e3d5-4480-8aa5-1233ddb61421',
             'Gov-*: Gov-Client-Connection-Method: WEB_APP_VIA_SERVER Gov-Client-Browser-JS-User-Agent:'.$_SERVER['HTTP_USER_AGENT'].' Gov-Client-Device-ID: '.$deviceIdentifier.' Gov-Client-Public-IP:'.$_SERVER['REMOTE_ADDR'].' Gov-Client-Timezone: '.$utcTimestamp.' Gov-Client-Public-Port:443 Gov-Client-Timezone:'.$localTimezoneOffset.' Gov-Client-User-IDs: my-application='.$user_email.'Gov-Vendor-Forwarded: by='.$_SERVER['REMOTE_ADDR'].'&for='.$req_for.' Gov-Vendor-License-IDs: my-licensed-software=8D7963490527D33716835EE7C195516D5E562E03B224E9B359836466EE40CDE1 Gov-Vendor-Product-Name: Cloud%20Billing%20Manager
 Gov-Vendor-Public-IP: '.$_SERVER['REMOTE_ADDR'].' Gov-Vendor-Version: my-web-app=2.1',
        );
    
        if ($govTestScenario !== null) {
            $headers[] = "Gov-Test-Scenario: $govTestScenario";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response, true);

        $obligations = $data['obligations'];

        foreach ($obligations as $obligation) {
            $data = array(
                'periodKey' => $obligation['periodKey'],
                'start' => $obligation['start'],
                'end' =>  $obligation['end'],
                'due' => $obligation['due'],
                'status' => $obligation['status'],
                'user_id' =>  $this->aauth->get_user()->id,
            );
                 $this->db->insert('geopos_vat_obligations', $data);

        }


    }

    public function VATsubmit(){
           $token = $this->getRefreshToken();
                   $deviceIdentifier = $this->generateUniqueDeviceIdentifier();
        $utcTimestamp = $this->generateUTCtimestamp();
        $localTimezoneOffset = $this->getLocalTimezoneOffset();
        $user_email = 'yamin.virk@gmail.com'; // replace this with logged in user email
        $req_by = '203.0.113.6'; //Server IP
        $req_for = '198.51.100.0'; // Vendor IP
            $vrn = "238848617"; 
            $curl = curl_init();

            $totalAcquisitionsExVAT = $this->input->post('totalAcquisitionsExVAT');
            $totalValueGoodsSuppliedExVAT = $this->input->post('totalValueGoodsSuppliedExVAT');
            $totalValuePurchasesExVAT = $this->input->post('totalValuePurchasesExVAT');
            $totalValueSalesExVAT = $this->input->post('totalValueSalesExVAT');
            $netVatDue = $this->input->post('netVatDue');
            $vatReclaimedCurrPeriod = $this->input->post('vatReclaimedCurrPeriod');
            $totalVatDue = $this->input->post('totalVatDue');
            $vatDueAcquisitions = $this->input->post('vatDueAcquisitions');
            $vatDueSales = $this->input->post('vatDueSales');
            $periodKey = $this->input->post('periodKey');

            $postData = array(
                'periodKey' => $periodKey,
                'vatDueSales' => $vatDueSales,
                'vatDueAcquisitions' => $vatDueAcquisitions,
                'totalVatDue' => $totalVatDue,
                'vatReclaimedCurrPeriod' => $vatReclaimedCurrPeriod,
                'netVatDue' => $netVatDue,
                'totalValueSalesExVAT' => $totalValueSalesExVAT,
                'totalValuePurchasesExVAT' => $totalValuePurchasesExVAT,
                'totalValueGoodsSuppliedExVAT' => $totalValueGoodsSuppliedExVAT,
                'totalAcquisitionsExVAT' => $totalAcquisitionsExVAT,
                'finalised' => true,
            );

            $url = "https://test-api.service.hmrc.gov.uk/organisations/vat/$vrn/returns";
            $headers = array(
                "Accept: application/vnd.hmrc.1.0+json",
                "Authorization: Bearer " . $token,
                "Client-ID: KxYwSNDont3Pq4hJT1GRutoCSovc",
                "Content-Type: application/json", 
                 'Gov-*: Gov-Client-Connection-Method: WEB_APP_VIA_SERVER Gov-Client-Browser-JS-User-Agent:'.$_SERVER['HTTP_USER_AGENT'].' Gov-Client-Device-ID: '.$deviceIdentifier.' Gov-Client-Public-IP:'.$_SERVER['REMOTE_ADDR'].' Gov-Client-Timezone: '.$utcTimestamp.' Gov-Client-Public-Port:443 Gov-Client-Timezone:'.$localTimezoneOffset.' Gov-Client-User-IDs: my-application='.$user_email.'Gov-Vendor-Forwarded: by='.$_SERVER['REMOTE_ADDR'].'&for='.$req_for.' Gov-Vendor-License-IDs: my-licensed-software=8D7963490527D33716835EE7C195516D5E562E03B224E9B359836466EE40CDE1 Gov-Vendor-Product-Name: Cloud%20Billing%20Manager
 Gov-Vendor-Public-IP: '.$_SERVER['REMOTE_ADDR'].' Gov-Vendor-Version: my-web-app=2.1',

            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POST => true, // Set as a POST request
                CURLOPT_POSTFIELDS => json_encode($postData), // Set the POST parameters
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response, true);

            if($data){
                            $postData = array(
                'periodKey' => $periodKey,
                'vatDueSales' => $vatDueSales,
                'vatDueAcquisitions' => $vatDueAcquisitions,
                'totalVatDue' => $totalVatDue,
                'vatReclaimedCurrPeriod' => $vatReclaimedCurrPeriod,
                'netVatDue' => $netVatDue,
                'totalValueSalesExVAT' => $totalValueSalesExVAT,
                'totalValuePurchasesExVAT' => $totalValuePurchasesExVAT,
                'totalValueGoodsSuppliedExVAT' => $totalValueGoodsSuppliedExVAT,
                'totalAcquisitionsExVAT' => $totalAcquisitionsExVAT,
                'finalised' => true,
                'is_complete' => 1,
                'user_id' =>  $this->aauth->get_user()->id,
            );
                                 $this->db->insert('geopos_vat_obligations_sent', $postData);

            }

        foreach ($data as $payment) {
            $data = array(
                'processingDate' => $payment['processingDate'],
                'formBundleNumber' => $payment['formBundleNumber'],
                'paymentIndicator' =>  $payment['paymentIndicator'],
                'chargeRefNumber' => $payment['chargeRefNumber'],
                'periodKey' => $periodKey,
                'user_id' =>  $this->aauth->get_user()->id,
                'obligation_id' => 1,
            );
                 $this->db->insert('geopos_vat_obligations_submitted', $data);

        }



    }



}
