<?php

error_reporting(E_ALL);
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
include '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
#include '../lib/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';

connect();
loginTest();

$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Data_Check($_GET['function']);

/**
 * 
 * Phenotype Experiment Results
 *
 */

class Data_Check
{
  /**
   * Using the class's constructor to decide which action to perform
   * @param unknown_type $function
   */
  public function __construct($function = null) {
    switch($function)
      {
      case 'typeDatabase':
        $this->type_Database(); /* update database */
        break;
      default:
        $this->typeExperimentCheck(); /* intial case*/
        break;
      }
  }

/**
 * check experiment data before loading into database
 */
private function typeExperimentCheck()
        {
                global $config;
                include($config['root_dir'] . 'theme/admin_header.php');
                echo "<h2>CSR Phenotype Data Validation</h2>";
                $this->type_Experiment_Name();
                $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
        }

/**
 * check experiment data before loading into database
 */
 private function type_Experiment_Name() {
?>
   <script type="text/javascript">
     function update_database(filepath, filename, username, rawdatafile) {
     var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&expdata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&raw_data_file=' + rawdatafile;
     // Opens the url in the same window
     window.open(url, "_self");
   }
   </script>
   <style type="text/css">
     th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
     table {background: none; border-collapse: collapse}
     td {border: 0px solid #eee !important;}
     h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
   </style>
<?php
  global $config;
  $row = loadUser($_SESSION['username']);
  $username=$row['name'];
  $tmp_dir="uploads/tmpdir_".$username."_".rand();
  $meta_paht= "uploads/".$_FILES['file']['name'][0];
  $raw_path= "../raw/phenotype/".$_FILES['file']['name'][1];
  if (!empty($_FILES['file']['name'][1])) {
         if (move_uploaded_file($_FILES['file']['tmp_name'][1], $raw_path) !== TRUE) {
             echo "<font color=red><b>Oops!</b></font> Your raw data file <b>"
             .$_FILES['file']['name'][1]."</b> was not saved in directory ".$config['root_dir']."raw/ and
             will be lost.  Please <a href='".$config['base_url']."feedback.php'>contact the 
             programmers</a>.<p>";
         } else {
             echo "uploaded file - " . $_FILES['file']['name'][1] . "<br/>";
             //check file for readability
             $i = 0;
             if (($reader = fopen($raw_path, "r")) == false) {
               die("error - can not read file $raw_path<br>\n");
             }
             while ($line = fgets($reader)) {
               $temp = str_getcsv($line,"\t");
               if ($size == 0) {
                 $size = count($temp);
               } else {
                 $size_t = count($temp);
                 if ($size != $size_t) {
                   echo "error $size $size_t<br>\n";
                 }
               }
               $i++;
             }
             fclose($reader);
             echo "$i lines of size $size in file<br><br>\n";
             $objPHPExcel = new PHPExcel();
             $objPHPExcel->setActiveSheetIndex(0);
             $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'test');
             #$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
             $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
             $objWriter->save('/tmp/tht/testfile.xls');
         }
         umask(0);
  } else {
    echo "missing Meta file\n";
  }
  if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
      mkdir($tmp_dir, 0777);
  }
  $target_path=$tmp_dir."/";
  if ($_FILES['file']['name'][0] == ""){
     error(1, "No File Uploaded");
     print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
  } else {
    $uploadfile=$_FILES['file']['name'][0];
    $rawdatafile = $_FILES['file']['name'][1];

    $uftype=$_FILES['file']['type'][0];
    if (strpos($uploadfile, ".xlsx") === FALSE) {
             error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
             print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    } else {
      if (move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile) !== TRUE) {
          echo "error - could not upload file $uploadfile<br>\n";
      } else {
          echo "uploaded file - " . $_FILES['file']['name'][0] . "<br>\n";
      }
               $metafile = $target_path.$uploadfile;
               /* Read the Means file */
               $objPHPExcel = PHPExcel_IOFactory::load($metafile);
               $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
               if ($sheetData[1]["A"] == "Parameter") {
                 echo "header is okay<br>\n";
               }
               if ($sheetData[2]["A"] == "Trial Name") {
                 $trial_name = $sheetData[2]["B"];
               } else {
                 $temp = $sheetData[2]["A"];
                 echo "expected \"Trial Name\" found $temp<br>\n";
               }
               if ($sheetData[3]["A"] == "Upwelling / Downwelling") {
                 $trial_name = $sheetData[3]["B"];
               } else {
                 $temp = $sheetData[3]["A"];
                 echo "expected \"Upwelling \/ Downwelling\" found $temp<br>\n";
               }
    }
  }

}

}