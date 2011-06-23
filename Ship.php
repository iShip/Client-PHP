<?php
//Functions for api connection
function wkday($tstamp = 0){
	$ts_len = strlen($tstamp);
	$ts = (int)$tstamp;
	$ts = ( $ts > 0 && $ts_len == 10 ? $tstamp : mktime(1, 1, 1, date("n"), date("j")+1, date("Y")) );
	
	$rt = getdate($ts);
	
	$by = $rt['year'];//build year
	$bm = $rt['mon']; // build month
	$bd = $rt['mday'];//build day
	
	if($rt['wday'] == 0 || $rt['wday'] == 6){
		//is weekday
		if($rt['wday'] == 0){
			//
			$bd += 1;
			
		}
		if($rt['wday'] == 6){
			//
			$bd += 2;
		}
	}
	$otime = mktime(date('h', $ts), date('i', $ts), date('s', $ts), $bm, $bd, $by);
	return $otime;
}
function roundup($val){
	$out = ($val > (int)$val ? (int)$val + 1 : (int)$val);
	return($out);
}


	function sendData($data = array()){
		//
		$apiData = urlencode((is_array($data)? json_encode($data) : $data));

		$apiData = "json" . "=" . $apiData;
		
		
		if(!$ch=curl_init()){
			$error="Curl is not initialized.";
			return false;
		}else{
			curl_setopt($ch, CURLOPT_URL,"http://usa.api.inxpressusa.com/api/json.php");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $apiData);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			if(curl_error($ch) != ""){
				echo $error = "Error with Curl installation: " . curl_error($ch) . "<br>";
				return false;
			}else{
				curl_close($ch);
				return $result;
			}
		}
	}



$ship_request = array(
			'auth' => array(
							"key" => '************', //YOUR API Auth Key
							"token" => '*************', //Your API AUTH Token
							"mode" => 'test', //Auth Mode {test, live}
							"ref" => ''//System Ref
							),
			"action" => "ship", //Connection Action
			"ship" => array(
							"type" => "p",//p for package, l for letter //Package Type
							"date" => date("Y-m-d",wkday()),//Shipment date {between current date and 9 days from now. only days of the week}
							"org" => array(
											"company" => "911 Computer Repair",//Shipper Company
											"name" => "Adam Spencer",//Shipper Name
											"address" => "14572 South 790 West",//Shipper Address
											"city" => "Bluffdale",//Shipper City
											"division" => "UT",//Shipper State
											"postal" => "84065",//Shipper Postal Code
											"country" => "US",//Shipper Country Code
											"email" => "adam@911computerrepair.com",//Shipper Email
											"phone" => "8012442591"//Shipper Phone
											),
							"dest" => array(
											"company" => "Express Worldwide Ltd",//Destination Company
											"name" => "John Doe",//Destination Name
											"address" => "1 Fieldhouse Road",//Destination Address
											"city" => "Rochdale",//Destination City
											"division" => "Lancashire",//Destination State
											"postal" => "OL12 0AD",//Destination Zip
											"phone" => "8005551234", //Destination Phone (6 to 20 numbers)
											"country" => "GB" //Destination Country Code
											),
							"package" => array(
											"weight" => roundup(8.25),//Package Wieght
											"depth" => roundup(6),//Package depth / length
											"width" => roundup(12.25),//Package width
											"height" => roundup(6),//Package Height
											"contents" => "items that are in the box",//Package contents
											"note" => ""//Package notes
											),
							"duti" => array(
												"is" => true,//true or false
												"value" => 13,	//Dutiable value
												"party" => "r", //Party that pays duty value {s or r}
												"filing" => array(
																	"type" => "ftr",//duty - Type {ftr, itn, ein}
																	"ftsr" => "30.37(a)"//duty - Ftsr code
																	//"itn" => "X12345671234567",//duty - ITN code
																	//"ein" => ""//duty - EIN Number
																	)
												
												),
							"ins" => array(
											"use" => false,//bool variable
											"value" => 0//insurance value
											)
							)
			);



$ship_responce = sendData($ship_request);
$ship = json_decode($ship_responce);


	//charge
	$cost = (string)$ship->res->status->desc; //Shipment generated.
	//charge
	$cost = (real)$ship->res->rate->total;
	//desc
	$service_desc = (string)$ship->res->service->name;
	
	//Airbill
	$airbill = (string)$ship->res->airbill;
	//imgType
	$img_type = (string)$ship->res->type;
	//Airbill Image
	$img_src = (string)$ship->res->img;
	
	//now we generate the label
	$im = imagecreatefromstring($img_src);
	if ($im !== false) {
		//now we can save or display
		$img_action = 'save';
		switch($img_action){
			case "show":
				//this will show the image
				//But nothing mush be returned before for this to work
				header('Content-type: image/png');
				echo base64_decode($img_src);
				break;
			case "save":
			default:
				//this will save the image
				$saveto = time() . ".PNG";
				imagepng($im, $saveto);
				break;
		}
	}else{
		echo 'An error occurred.';
	}
	



	echo "<p>\n JSON Quote Responce: <p>" . $ship_responce;
	echo "<p>Shipment Data Format:</p><pre>";
	print_r($ship);
	echo "</pre>";
?>