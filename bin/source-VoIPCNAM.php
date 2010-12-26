<?php
//this file is designed to be used as an include that is part of a loop.
//If a valid match is found, it should give $caller_id a value
//available variables for use are: $thenumber
//retreive website contents using get_url_contents($url);
//This module is for use with CallerID Superfecta 2.0.

// --VoIPCNAM.com Module for Superfecta 2.0--
// --Modified by Michael Ruge <michael@winmac.com>--

//This module was modified from the original Google Module for Superfecta 2.0 to work with the VoIPCNAM.com
//lookup service.  I take no responsibilities for anything that may go wrong with this script or your PBX.
//If the internet crashes or the earth caves in on itself as a direct result of running this script, please
//do not hold me responsible.

//INSTALLATION INSTRUCTIONS
//
// 1. Open FreePBX and goto the CID Superfecta Module. You can get this API Key from here:  http://www.voipcnam.com/index.php?page=account_api
//
// 2. Check the 'Check for Data Source File updates online', and choose this source in dropdown list.
//
// 3. Enter your API key. You can get it from here:  http://www.voipcnam.com/index.php?page=account_api
//
// 4. Enable the service and set the priority you want to use it.  I have mine set as the last item as
//    I want to exaust all other searches before using this service.
//
// 5. Test it in the debug window.  Please note that this will charge your VoIPCNAM account even when
//    using the test number provided by VoIPCNAM.  It will also use your credits with VoIPCNAM everytime
//    you test any other number in the debug window.  I recommend disabling it while testing other providers
//    and then enabling it when you are ready to use it.
//
//    Enjoy and drop me a note if you have any other questions.


//configuration / display parameters
//The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
$source_desc = "http://www.voipcnam.com - This module provides lookups from the VoIPCNAM.com lookup service.<br><br>This data source requires Superfecta Module version 2.2.1 or higher.";
$source_param = array();
$source_param['API_Key']['desc'] = 'API Key REQUIRED. Get it from voipcnam.com.';
$source_param['API_Key']['type'] = 'text';

// DO NOT EDIT BELOW THIS LINE
//run this if the script is running in the "get caller id" usage mode.
if($usage_mode == 'get caller id')
{
	if($debug)
	{
		print "Searching VoIPCNAM ... ";
	}
	$number_error = false;
	
	//check for the correct 11 digits in US/CAN phone numbers in international format.
	// country code + number
	if (strlen($thenumber) == 11)
	{
		if (substr($thenumber,0,1) == 1)
		{
			$thenumber = substr($thenumber,1);
		}
		else
		{
			$number_error = true;
		}

	}
	// international dialing prefix + country code + number
	if (strlen($thenumber) > 11)
	{
		if (substr($thenumber,0,3) == '001')
		{
			$thenumber = substr($thenumber, 3);
		}
		else
		{
			if (substr($thenumber,0,4) == '0111')
			{
				$thenumber = substr($thenumber,4);
			}			
			else
			{
				$number_error = true;
			}
		}
	}	
	// number
      if(strlen($thenumber) < 10)
	{
		$number_error = true;
	}

	if(!$number_error)
	{
		$npa = substr($thenumber,0,3);
		$nxx = substr($thenumber,3,3);
		$station = substr($thenumber,6,4);
		
		// Check for Toll-Free numbers
		$TFnpa = false;
		if($npa=='800'||$npa=='866'||$npa=='877'||$npa=='888')
		{
			$TFnpa = true;
		}
		
		// Check for valid US NPA
		$npalistUS = array(
			"201", "202", "203", "205", "206", "207", "208", "209", "210", "212",
			"213", "214", "215", "216", "217", "218", "219", "224", "225", "228",
			"229", "231", "234", "239", "240", "242", "246", "248", "251", "252",
			"253", "254", "256", "260", "262", "264", "267", "268", "269", "270",
			"276", "281", "284", "301", "302", "303", "304", "305", "307", "308",
			"309", "310", "312", "313", "314", "315", "316", "317", "318", "319",
			"320", "321", "323", "325", "330", "331", "334", "336", "337", "339",
			"340", "343", "345", "347", "351", "352", "360", "361", "386", "401", "402",
			"404", "405", "406", "407", "408", "409", "410", "412", "413", "414",
			"415", "417", "419", "423", "424", "425", "430", "432", "434", "435",
			"440", "441", "443", "456", "469", "473", "478", "479", "480", "484",
			"500", "501", "502", "503", "504", "505", "507", "508", "509", "510",
			"512", "513", "515", "516", "517", "518", "520", "530", "540", "541",
			"551", "559", "561", "562", "563", "567", "570", "571", "573", "574",
			"575", "580", "585", "586", "600", "601", "602", "603", "605", "606",
			"607", "608", "609", "610", "612", "614", "615", "616", "617", "618",
			"619", "620", "623", "626", "630", "631", "636", "641", "646", "649",
			"650", "651", "660", "661", "662", "664", "670", "671", "678", "682",
			"684", "700", "701", "702", "703", "704", "706", "707", "708", "710",
			"712", "713", "714", "715", "716", "717", "718", "719", "720", "724",
			"727", "731", "732", "734", "740", "754", "757", "758", "760", "762",
			"763", "765", "767", "769", "770", "772", "773", "774", "775", "779",
			"781", "784", "785", "786", "787", "801", "802", "803", "804", "805",
			"806", "808", "809", "810", "812", "813", "814", "815", "816", "817",
			"818", "828", "829", "830", "831", "832", "843", "845", "847", "848",
			"850", "856", "857", "858", "859", "860", "862", "863", "864", "865",
			"868", "869", "870", "876", "878", "900", "901", "903", "904", "906",
			"907", "908", "909", "910", "912", "913", "914", "915", "916", "917",
			"918", "919", "920", "925", "928", "931", "936", "937", "939", "940",
			"941", "947", "949", "951", "952", "954", "956", "970", "971", "972",
			"973", "978", "979", "980", "985", "989",
			"800", "866", "877", "888"
		);
		
		$validnpaUS = false;
		if(in_array($npa, $npalistUS))
		{
			$validnpaUS = true;
		}
		
		// Check for valid CAN NPA
		$npalistCAN = array(
			"204", "226", "249", "250", "289", "306", "343", "365", "403", "416", "418", "438", "450",
			"506", "514", "519", "579", "581", "587", "604", "613", "647", "705", "709",
			"778", "780", "807", "819", "867", "873", "902", "905",
			"800", "866", "877", "888"
		  );
		
		$validnpaCAN = false;
		if(in_array($npa, $npalistCAN))
		{
			$validnpaCAN = true;
		}
	
		if(!$TFnpa && (!$validnpaUS && !$validnpaCAN))
		{
			$number_error = true;
		}
	
		if($number_error)
		{
			if($debug)
			{
				print "Skipping Source - Not a valid US/CAN number: ".$thenumber."<br>\n";
			}
		}
		else
		{
			$url="http://query.voipcnam.com/query.php?api_key=".$run_param['API_Key']."&number=1$thenumber";
			$value = get_url_contents($url);
			
			if (strlen($value) > 1)
			{
				$caller_id = strip_tags($value);
			}
			else if($debug)
			{
				print "not found<br>\n";
			}
		}
	}
	else
	{
		if($debug)
		{
			print "Skipping Source - Non NANP number: ".$thenumber."<br>\n";
		}

	}
}
?>