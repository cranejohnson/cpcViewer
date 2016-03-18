<?php
date_default_timezone_set("UTC");
error_reporting(0);

chdir(dirname(__FILE__));
chdir('cpcData');

if(!($days = $_GET['days'])) $days = 7;



echo "CPC Fetch Starting at: ".date('Y-m-d H:i')."\n";


$path = getcwd();

function getCPC($name){
  //Delete old file if it exists......
  unlink('doc.kml');
    if(file_exists("$name.kml")){
      echo "Data exists for $name.kml...skipping download\n";
      return;
    }
    $location = "ftp://ftp.cpc.ncep.noaa.gov/GIS/us_tempprcpfcst/$name.kmz";
  $kmz = file_get_contents($location);
  if(!$kmz){
    echo "No file avaialable for $name\n";
    return false;
  }
  file_put_contents("$name.kmz", $kmz);
  echo "Data for $name\n";
  exec("unzip $name.kmz", $result, $returnval);
  rename("doc.kml", "$name.kml");
}

function getHazards(){

    $files = array(
        "prcp3-7" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Prcp_D3_7.kml',
        "soil3-7" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Soils_D3_7.kml',
        "temp3-7" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Temp_D3_7.kml',
        "prcp7-14" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Prcp_D8_14.kml',
        "soil7-14" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Soils_D8_14.kml',
        "temp7-14" => 'http://www.cpc.ncep.noaa.gov/products/predictions/threats/Temp_D8_14.kml',
        "monthly_temp" => 'http://www.cpc.ncep.noaa.gov/products/predictions/30day/lead14_temp.kml',
        "monthly_prcp" => 'http://www.cpc.ncep.noaa.gov/products/predictions/30day/lead14_prcp.kml');

    foreach($files as $type=>$file){

        //Delete old file if it exists......
        echo "Retrieving $type as $file \n";

        $kml = file_get_contents($file);
        if(!$kml){
            echo "No file Hazard available for $type\n";
            continue;
        }
        preg_match('/Created:(.+)Valid/',$kml,$match);

   #     if($type == 'monthly_temp') preg_match('/Valid:(.+)</',$kml,$match);
   #     if($type == 'monthly_prcp') preg_match('/Valid:(.+)</',$kml,$match);

        $createdDate = strtotime($match[1]);
        $fileDate = date('Ymd',$createdDate);
        file_put_contents($type."_".$fileDate.".kml",$kml);
  }
}




for($i=0;$i<=intval($days);$i++){
  $subdays = 86400*$i;
  $date = date('Ymd',intval(time()-$subdays));
  echo "Grabbing data for $date\n";
  $filename = "814temp_$date";
  getCPC("814temp_$date");
  getCPC("610temp_$date");
  getCPC("814prcp_$date");
  getCPC("610prcp_$date");


}
getHazards();
$csv = glob("*.kmz");


// Delete all the rest.
#array_map('unlink', $csv);
?>
