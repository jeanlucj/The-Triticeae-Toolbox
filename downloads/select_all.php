<?php
/**
 * Download Gateway
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 * 
 */
// |                                                                      |
// | The purpose of this script is to provide the user with an interface  |
// | for downloading certain kinds of files from THT.                     |
// +----------------------------------------------------------------------+
// | Authors:  Gavin Monroe <gemonroe@iastate.edu>  						|
// | Updated: December 2008 by Julie A Dickerson, julied@iastate.edu	  |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Change log								  |
// | 2/8/11:  DEM - Include markers with MAF = 0 too if user wishes.      |
// | 1/5/01:  JLee - Add support to generate datafile for Tassel V3       |  
// |                                                                      |
// | 2/28/09: removed table summarizing all allelles to avoid timeout	  |
// |          problems when getting SNP data across multiple programs
// | 5/20/09: added in tassel support functionality and commented out
// | 			routines for QTLMiner
// | September 2009: added in the ability put check lines into the output |
// |			file for traits; if there are multiple check lines of the |
// | 			same name, then the mean is used.	Also added in seesions  |
// | 			to verify that data is available for a user.			  |
// +----------------------------------------------------------------------+
set_time_limit(0);

// For live website file
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

require_once $config['root_dir'].'includes/MIME/Type.php';
require_once $config['root_dir'].'includes/File_Archive/Archive.php';

// connect to database
connect();

new Downloads($_GET['function']);

/** Using a PHP class to implement the "Download Gateway" feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Downloads
{   
    /**
     * delimiter used for output files
     */
    public $delimiter = "\t";
    
    /** 
     * Using the class's constructor to decide which action to perform
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {	
        switch($function)
        {
            case 'type1':
                $this->type1();
                break;
            case 'type1experiments':
                $this->type1_experiments();
                break;
            case 'step1dataprog':
                $this->step1_dataprog();
                break;
            case 'step1lines':
                $this->step1_lines();
                break;
            case 'step1locations':
                $this->step1_locations();
                break;
            case 'step2locations':
                $this->step2_locations();
                break;
			case 'step3locations':
			    $this->step3_locations();
			    break;
			case 'step5locations':
			    $this->step5_locations();
			    break;
			case 'step5programs':
			     $this->step5_programs();
			     break;
			case 'step2lines':
				$this->step2_lines();
				break;
			case 'step3lines':
				$this->step3_lines();
				break;
		 	case 'step4lines':
				$this->step4_lines();
				break;
			case 'step1breedprog':
				$this->step1_breedprog();
				break;
			case 'step1phenotype':
				$this->step1_phenotype();
				break;
			case 'step2phenotype':
				$this->step2_phenotype();
				break;
			case 'step3phenotype':
				$this->step3_phenotype();
				break;
			case 'step4phenotype':
				$this->step4_phenotype();
				break;
			case 'step5phenotype':
			    $this->step5_phenotype();
			    break;
			case 'step1yearprog':
			    $this->step1_yearprog();
			    break;
			case 'type1traits':
				$this->type1_traits();
				break;
			case 'type1markers':
				$this->type1_markers();
				break;
			case 'type2markers':
			    $this->type2_markers();
			    break;
			case 'refreshtitle':
			    echo $this->refresh_title();
			    break;
			default:
				$this->type1_select();
				break;
				
		}	
	}

	/**
	 * load header and footer then check session to use existing data selection
	 */
	private function type1_select()
	{
		global $config;
                include($config['root_dir'].'theme/normal_header.php');
		$phenotype = "";
                $lines = "";
		$markers = "";
		$saved_session = "";
		$this->type1_checksession();
		include($config['root_dir'].'theme/footer.php');
	}	
	
	/**
	 * When there is no data saved in session this handles outputting the header and footer and calls the first real action of the type1 download.
	 */ 
	private function type1()
	{
		global $config;
		#include($config['root_dir'].'theme/normal_header.php');

		#echo "<h2>Tassel Download</h2>";
		#echo "<p><em>Select multiple options by holding down the Ctrl key while clicking.
		#</em></p>";
		unset($_SESSION['selected_lines']);
		unset($_SESSION['phenotype']);
                unset($_SESSION['selected_traits']);
                unset($_SESSION['selected_trials']);
		unset($_SESSION['clicked_buttons']);
		
		?>
		<p>1.
		<select name="select1" onchange="javascript: update_select1(this.options)">
		<option value="BreedingProgram">Program</option>
		<option value="Lines">Lines</option>
		<option value="Locations">Locations</option>
		<option value="Phenotypes">Trait Category</option>
		</select></p>
		<?php 
		$this->step1_breedprog();
		$footer_div = 1;
        #	include($config['root_dir'].'theme/footer.php');
	}
	
	/**
	 * Checks the session variable, if there is lines data saved then go directly to the lines menu
	 */
	private function type1_checksession()
    {
            ?>
            <style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div id="title">
		<?php
            $phenotype = "";
            $lines = "";
            $markers = "";
            $saved_session = "";
		    $message1 = $message2 = "";

            if (isset($_SESSION['phenotype'])) {
                    $tmp = count($_SESSION['phenotype']);
                    if ($tmp==1) {
                        $saved_session = "$tmp phenotype ";
                    } else {
                        $saved_session = "$tmp phenotypes ";
                    }
                    $message2 = "download phenotype and genotype data";
                    $phenotype = $_SESSION['phenotype'];
            } else {
				$message1 = "0 phenotypes";
				$message2 = " download genotype data";
			}
            if (isset($_SESSION['selected_lines'])) {
                    $countLines = count($_SESSION['selected_lines']);
                    if ($saved_session == "") {
                        $saved_session = "$countLines lines";
                    } else {
                        $saved_session = $saved_session . ", $countLines lines";
                    }
                    $lines = $_SESSION['selected_lines'];
            }
            if (isset($_SESSION['clicked_buttons'])) {
                    $tmp = count($_SESSION['clicked_buttons']);
                    $saved_session = $saved_session . ", $tmp markers";
                    $markers = $_SESSION['clicked_buttons'];
            } else {
			    if ($message2 == "") {
			      $message1 = "0 markers ";
			      $message2 = "for all markers.";
			    } else {
			  	  $message1 = $message1 . ", 0 markers ";
			  	  $message2 = $message2 . " for all markers";
				}
			}	
            $this->refresh_title();
         ?>        
        </div>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
		<p>1. 
		<select name="select1" onchange="javascript: update_select1(this.options)">
		  <option value="BreedingProgram">Program</option>
		  <option value="Lines">Lines</option> 
		  <option value="Locations">Locations</option>
		  <option value="Phenotypes">Trait Category</option>
		</select></p>
		        <script type="text/javascript" src="downloads/downloads.js"></script>
                <?php 
                $this->type1_breeding_programs_year();
                ?>
                </div>
                
                <?php
        }

    /**
     * 1. display a spinning activity image when a slow function is running
     * 2. show button to clear sessin data
     * 3. show button to save current selection
     */    
    private function refresh_title() {
      $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
      $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
      ?>
      <h2>Select Lines, Traits, and Trials</h2>
      <p>
      Select genotype and phenotype data for analysis or download. To selecting lines without phenotypes use
      <a href=downloads/select_genotype.php>Select Genotype</a>.
      <em>Select multiple options by holding down the Ctrl key while clicking.</em> 
      <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
      <?php 
      $selection_ready = 0;
      if (isset($_SESSION['selected_lines'])) {
        ?>
        <input type="button" value="Clear current selection" onclick="javascript: use_normal();"/>
        <?php 
      }
      if ($command == "save") {
        if (empty($_GET['lines'])) {
          if ((!empty($_GET['pi'])) && (!empty($_GET['exps']))) {
            $phen_item = $_GET['pi'];
            $experiments = $_GET['exps'];
          } else {
            echo "error phenotype and experiments not set";
          }
          if ((($subset == "yes") || ($subset == "comb")) && count($_SESSION['selected_lines'])>0) {
            $lines = $_SESSION['selected_lines'];
            $lines_str = implode(",", $lines);
            $count = count($_SESSION['selected_lines']);
          } else {
            $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
            FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
            WHERE
            pd.tht_base_uid = tb.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND lr.line_record_uid = tb.line_record_uid
            AND pd.phenotype_uid IN ($phen_item)
            AND tb.experiment_uid IN ($experiments)
            ORDER BY lr.line_record_name";
            $lines = array();
            $res = mysql_query($sql) or die(mysql_error() . $sql);
            while ($row = mysql_fetch_assoc($res))
            {
              array_push($lines,$row['id']);
            }
            $lines_str = implode(",", $lines);
            $count = count($lines);
          }
          //overide these setting is radio button checked
          if ($subset == "no") {
            $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
            FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
            WHERE
            pd.tht_base_uid = tb.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND lr.line_record_uid = tb.line_record_uid
            AND pd.phenotype_uid IN ($phen_item)
            AND tb.experiment_uid IN ($experiments)
            ORDER BY lr.line_record_name";
            $lines = array();
            $res = mysql_query($sql) or die(mysql_error() . $sql);
            while ($row = mysql_fetch_assoc($res))
            {
              array_push($lines,$row['id']);
            }
            $lines_str = implode(",", $lines);
            $count = count($lines);
          } elseif ($subset == "comb") {
            $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
            FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
            WHERE
            pd.tht_base_uid = tb.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND lr.line_record_uid = tb.line_record_uid
            AND pd.phenotype_uid IN ($phen_item)
            AND tb.experiment_uid IN ($experiments)
            ORDER BY lr.line_record_name";
            $res = mysql_query($sql) or die(mysql_error() . $sql);
            while ($row = mysql_fetch_assoc($res))
            {
              $line_uid = $row['id'];
              if (!in_array($line_uid,$lines)) {
                array_push($lines,$row['id']);
              }
            }
            $lines_str = implode(",", $lines);
          } elseif ($subset == "yes") {
            $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
            FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
            WHERE
            pd.tht_base_uid = tb.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND lr.line_record_uid = tb.line_record_uid
            AND pd.phenotype_uid IN ($phen_item)
            AND tb.experiment_uid IN ($experiments)
            ORDER BY lr.line_record_name";
            $res = mysql_query($sql) or die(mysql_error() . $sql);
            while ($row = mysql_fetch_assoc($res))
            {
              $temp[] = $row['id'];
            }
            $lines = array_intersect($lines, $temp);
            $lines_str = implode(",", $lines);
          }
        } else {
          $lines_str = $_GET['lines'];
          $lines = explode(',', $lines_str);
        }
        $_SESSION['selected_lines'] = $lines;
          
        if (isset($_GET['pi'])) {
          $_SESSION['phenotype'] = $_GET['pi'];
          $phenotype_ary = explode(",",$_SESSION['phenotype']);
          $_SESSION['selected_traits'] = $phenotype_ary;
        } else {
          echo "error - no selection found";
        }
        if (isset($_GET['exps'])) {
          $trials_ary = explode(",",$_GET['exps']);
          $_SESSION['selected_trials'] = $trials_ary;
          $_SESSION['experiments'] = $_GET['exps'];
        } else {
          echo "error - no trials selection found";
        }


        $username=$_SESSION['username'];
        if ($username) {
          store_session_variables('selected_lines', $username);
        }
      } elseif ($selection_ready) {
        ?>
        <!-- input type="button" value="Save current selection" onclick="javascript: load_title('save');"/-->
       <?php
      }
      ?>
      </p>
      <?php 
    }
    
    /**
     * use this download when selecting program and year
     * @param string $version Tassel version of output
     */
    private function type1_session($version)
	{
	    $experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
	    $datasets_exp = "";
		if (isset($_SESSION['selected_lines'])) {
			$selectedcount = count($_SESSION['selected_lines']);
			$lines = $_SESSION['selected_lines'];
			$lines_str = implode(",", $lines);
		} else {
			$lines = "";
			$lines_str = "";
		}
		if (isset($_SESSION['clicked_buttons'])) {
		    $selectcount = $_SESSION['clicked_buttons'];
		    $markers = $_SESSION['clicked_buttons'];
		    //$markers = implode(",", $_SESSION['clicked_buttons']);
		} else {
		    $markers = array();
		}
		if (isset($_SESSION['phenotype'])) {
		    $phenotype = $_SESSION['phenotype'];
		} else {
		    $phenotype = "";
		}
		
		//get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
		FROM experiments e, experiment_types as et, line_records as lr, tht_base as tb
		WHERE
		e.experiment_type_uid = et.experiment_type_uid
		AND lr.line_record_uid = tb.line_record_uid
		AND e.experiment_uid = tb.experiment_uid
		AND lr.line_record_uid in ($lines_str)
		AND et.experiment_type_name = 'genotype'";
		$res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
		if (mysql_num_rows($res)>0) {
		 while ($row = mysql_fetch_array($res)){
		  $exp[] = $row["exp_uid"];
		 }
		 $experiments_g = implode(',',$exp);
		}
		
		$dir = '/tmp/tht/';
                $filename = 'THTdownload_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';

	        // File_Archive doesn't do a good job of creating files, so we'll create it first
                if(!file_exists($dir.$filename)){
                        $h = fopen($dir.$filename, "w+");
                        fclose($h);
                }
        $zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
        $subset = "yes";
        
        if (isset($_SESSION['phenotype'])) {
		  $zip->newFile("traits.txt");
		  $zip->writeData($this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset));
        }
        if ($version == "V2") {
          $zip->newFile("annotated_alignment.txt");
          $zip->writeData($this->type1_build_annotated_align($experiments_g));
        } elseif ($version == "V3") {
          $zip->newFile("geneticMap.txt");
          $zip->writeData($this->type1_build_geneticMap($experiments_g));
          $zip->newFile("snpfile.txt");
          $zip->writeData($this->type2_build_markers_download($lines,$markers,$dtype));
        } elseif ($version == "V4") {
          $zip->newFile("genotype_hapmap.txt");
          $zip->writeData($this->type3_build_markers_download($lines,$markers,$dtype));
        }
        $zip->newFile("allele_conflict.txt");
        $zip->writeData($this->type2_build_conflicts_download($lines,$markers));
        $zip->close();
        
        header("Location: ".$dir.$filename);
	}
	
	/**
	 * this is the main entry point when there are no lines saved in session variable
	 */
    private function type1_breeding_programs_year()
	{
		?>	
			<div id="step11">
			<table>
				<tr>
					<th>Breeding Program</th>
				</tr>
				<tr>
					<td>
						<select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php

		// Select breeding programs for the drop down menu
		$sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
				FROM CAPdata_programs WHERE program_type='breeding' ORDER BY name";

		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
			?>
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name']." (".$row['code'].")" ?></option>
			<?php
		}
		?>
						</select>
			</table>
			</div></div>
					
			<div id="step2" style="float: left; margin-bottom: 1.5em;">
			<p>2.
		<select name="select2">
		  <option value="BreedingProgram">Year</option>
		</select></p>
			<table>
					<tr>
					    <th>Year</th>
					</tr>
					<tr>
					<td>
						<select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php

		// set up drop down menu with data showing year
		// should this be phenotype experiments only? No

		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
				WHERE e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'phenotype'";
		if (!authenticate(array(USER_TYPE_PARTICIPANT,
					USER_TYPE_CURATOR,
					USER_TYPE_ADMINISTRATOR)))
			$sql .= " and data_public_flag > 0";
		$sql .= " GROUP BY e.experiment_year DESC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
		
<?php
	}
	
	/**
	 * starting with phenotype display phenotype categories
	 */
	private function step1_phenotype()
	{
		?>
		<div id="step11" style="float: left; margin-bottom: 1.5em;">
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Category</th>
		</tr>
		<tr><td>
			<select name="phenotype_categories" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_categories(this.options)">
                <?php
		$sql = "SELECT phenotype_category_uid AS id, phenotype_category_name AS name from phenotype_category";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
				<option value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
		?>
		</select>
		</td>
		</table>
		</div>
		<?php
	}

	/**
	 * starting with phenotype display phenotype items
	 */
	private function step2_phenotype()
    {  
		$phen_cat = $_GET['pc'];
		?>
		<p>2.
		<select name="select1">
		  <option value="BreedingProgram">Trait</option>
		</select></p>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Traits</th>
		</tr>
		<tr><td>
		<select id="traitsbx" name="phenotype_items" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_items(this.options)">
                <?php

		$sql = "SELECT phenotype_uid AS id, phenotypes_name AS name from phenotypes, phenotype_category
		 where phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid and phenotype_category.phenotype_category_uid in ($phen_cat)";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
		?>
		</select>
		</table>
		
		<?php
    }
    
    /**
     * starting with phenotype display trials
     */
	private function step3_phenotype()
    {  
		$phen_item = $_GET['pi'];
		$trait_cmb = (isset($_GET['trait_cmb']) && !empty($_GET['trait_cmb'])) ? $_GET['trait_cmb'] : null;
		if ($trait_cmb == "all") {
		   $any_ckd = ""; $all_ckd = "checked";
		} else {
		   $trait_cmb = "any";
		   $any_ckd = "checked"; $all_ckd = "";
		}
		?>
		<p>3.
		<select name="select1">
		  <option value="BreedingProgram">Trials</option>
		</select></p>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Trials</th>
		</tr>
		<tr><td>
		<select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_trial(this.options)">
                <?php

		$sql = "SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name, p.phenotype_uid 
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($phen_item)";
	 if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	 $sql .= " and data_public_flag > 0";
	 $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		  $exp_uid = $row['id'];
		  $pi = $row['phenotype_uid'];
		  $sel_list[$exp_uid] = $row['name'];
		  $pi_list[$exp_uid][$pi] = 1;        //*array of traits for each trial
		}
		$phen_array = explode(",",$phen_item);
		foreach ($sel_list as $id=>$name) {
		  $found = 1;
		  foreach ($phen_array as $item) {    //*check if trial contains all trait
		    if (!isset($pi_list[$id][$item])) {
		       $found = 0;
		    }
		  }
		  if ($found || ($trait_cmb == "any")) {
		  ?>
		  <option value="<?php echo $id ?>">
		  <?php echo $name ?>
		  </option>
		  <?php
		  }
		}
		?>
		</select>
		</table>
		<?php 
		$tmp = count($phen_array);
		if ($tmp > 1) {
		  ?>
		  <input type="radio" id="trait_cmb" value="all" <?php echo "$all_ckd"; ?> onclick="javascript: update_phenotype_trialb(this.value)">trials with all traits<br>
		  <input type="radio" id="trait_cmb" value="any" <?php echo "$any_ckd"; ?> onclick="javascript: update_phenotype_trialb(this.value)">trials with any trait<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display lines
     */
	private function step4_phenotype()
    {  
    	$phen_item = $_GET['pi'];
		$experiments = $_GET['e'];
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		$selected_lines = array();
		$_SESSION['phenotype'] = $phen_item; // Empty the session array.
		
		if (count($_SESSION['selected_lines']) > 0) {
		 $sub_ckd = ""; $all_ckd = "checked";
		} else {
		 $sub_ckd = "disabled"; $all_ckd = "checked";
		}
		if ($subset == "yes") {
		 $sub_ckd = "yes"; $yes_ckd = "checked";
		} elseif ($subset == "no") {
		 $sub_ckd = ""; $all_ckd = "checked";
		} elseif ($subset == "comb") {
		 $sub_ckd = ""; $cmb_ckd = "checked";
		}
		?>
		<p>4.
		<select name="select1">
		  <option value="BreedingProgram">Lines</option>
		</select></p>
		
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Lines</th>
		</tr>
		<tr><td>
		<select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
                <?php
        if ($sub_ckd == "checked") {
          $lines_list = array();
          $lines_new = "";
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
            <option selected value="<?php echo $row['id'] ?>">
            <?php echo $row['name'] ?>
            </option>
            <?php
          }
        } elseif ($cmb_ckd == "checked") {
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
           <option selected value="<?php echo $row['id'] ?>">
           <?php echo $row['name'] ?>
           </option>
           <?php
         }
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           $temp1 = $row['name'];
           $temp2 = $row['id'];
           if (isset($lines_list[$temp2])) {
           } else {
             if ($lines_new == "") {
               $lines_new = $temp1;
               ?>
               <option disabled="disabled">--added--
               </option>
               <?php
             }
             ?>
             <option selected value="<?php echo $row['id'] ?>">
             <?php echo $row['name'] ?>
             </option>
             <?php
           }
         }
        } else {
		$sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name 
	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr 
	 WHERE
	 pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND lr.line_record_uid = tb.line_record_uid
	 AND pd.phenotype_uid IN ($phen_item)
	 AND tb.experiment_uid IN ($experiments)
	 ORDER BY lr.line_record_name";
		//$_SESSION['selected_lines'] = array(); // Empty the session array.
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 //array_push($_SESSION['selected_lines'],$row['id']);
		 ?>
				<option selected value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
        }
		?>
		</select>
		</table>	
		<?php
		if (count($_SESSION['selected_lines']) > 0) {
		  ?>
                  Combine with currently selected lines:<br>
		  <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Replace<br>
		  <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Add (OR)<br>
                  <input type="radio" name="subset" id="subset" value="yes" <?php echo "$yes_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Intersect (AND)<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display marker data
     */
    private function step5_phenotype()
    {
     $phen_item = $_GET['pi'];
     $experiments = $_GET['e'];
     $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
     
     if (empty($_GET['lines'])) {
       if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
         $lines = $_SESSION['selected_lines'];
         $selectedlines = implode(",", $_SESSION['selected_lines']);
         $count = count($lines);
       } else {
         $lines = array();
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           array_push($lines,$row['id']);
         }
         $selectedlines = implode(",", $lines);
         $count = count($lines);
       }
       if ($subset == "comb") {
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           $line_uid = $row['id'];
           if (!in_array($line_uid, $lines)) {
              $lines[] = $row['line_record_uid'];
           }
         }
         $selectedlines = implode(",", $lines);
         $count = count($lines);
       } elseif ($subset == "yes") {
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           $temp[] = $row['id'];
         }
         array_intersect($lines, $temp);
         $selectedlines = implode(",", $lines);
         $count = count($lines);
       }
     } else {
         $selectedlines = $_GET['lines'];
         $lines = explode(',', $selectedlines);
         $count = count($lines);
     }
         
     echo "current data selection = $count lines";
     
     // initialize markers and flags if not already set
     $max_missing = 99.9;//IN PERCENT
     if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
      $max_missing = $_GET['mm'];
     if ($max_missing>100)
      $max_missing = 100;
     elseif ($max_missing<0)
     $max_missing = 0;
     $min_maf = 0.01;//IN PERCENT
     if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
      $min_maf = $_GET['mmaf'];
     if ($min_maf>100)
      $min_maf = 100;
     elseif ($min_maf<0)
     $min_maf = 0;
     
     /* $this->calculate_af($lines, $min_maf, $max_missing); */
     $traits_ary = explode(",",$phen_item);
     $count = count($traits_ary);
     echo ", $count traits";
     
     ?>
     <input type="hidden" name="subset" id="subset" value="yes" /><br>
     <input type="button" value="Save current selection" onclick="javascript: load_title('save');"/>
 
    <?php
    }
    
    /**
     * starting with year
     */
    private function step1_yearprog()
    {
    $CAPdata_programs = $_GET['bp'];
     ?>
    <div id="step2" style="float: left; margin-bottom: 1.5em;">
                        <p>2.
                <select name="select2">
                  <option value="BreedingProgram">Year</option>
                </select></p>

    <table id="phenotypeSelTab" class="tableclass1">
    <tr>
    <th>Year</th>
    </tr>
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
    <?php
    $sql = "SELECT e.experiment_year AS year 
    FROM experiments AS e, experiment_types AS et, datasets AS ds, datasets_experiments AS d_e
    WHERE e.experiment_type_uid = et.experiment_type_uid
    AND e.experiment_uid = d_e.experiment_uid
    AND d_e.datasets_uid = ds.datasets_uid
    AND et.experiment_type_name = 'phenotype'
    AND ds.CAPdata_programs_uid IN ('$CAPdata_programs')
    GROUP BY e.experiment_year DESC";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
    ?>
    <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
    <?php
    }
    ?>
    </select>
    </td>
    </table>
    </div>
    <?php
    }
    
    /**
     * starting with breeding program display breeding program and year
     */
	private function step1_breedprog()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
                $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
                ?>
                <div id="step11" style="float: left; margin-bottom: 1.5em;">
                <table>
                <tr>
                        <th>Breeding Program</th>
                </tr>
		<tr>
                                        <td>
                                                <select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
                <?php

                // Select breeding programs for the drop down menu
                $sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
                                FROM CAPdata_programs WHERE program_type='breeding' ORDER BY name";

                $res = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($res))
                {
                        ?>
                                <option value="<?php echo $row['id'] ?>"><?php echo $row['name']." (".$row['code'].")" ?></option>
                        <?php
                }
                ?>
                                                </select>
                                        </td>
                <?php
         }

     /**
     * starting with breeding program display year
     */
         private function step2_breedprog()
         {
               ?>
               <table>
               <tr>
                      <th>Year</th>
               <tr>
					<td>
                                                <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
                <?php

                // set up drop down menu with data showing year
                // should this be phenotype experiments only? No

                $sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
                                WHERE e.experiment_type_uid = et.experiment_type_uid
                                        AND et.experiment_type_name = 'phenotype'";
                if (!authenticate(array(USER_TYPE_PARTICIPANT,
                                        USER_TYPE_CURATOR,
                                        USER_TYPE_ADMINISTRATOR)))
                        $sql .= " and data_public_flag > 0";
                $sql .= " GROUP BY e.experiment_year DESC";
                $res = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($res)) {
                        ?>
                                <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
                        <?php
                }
                ?>
                                                </select>
                                        </td>
                                </tr>
                        </table>
<?php	
	}
	
	/**
	 * starting with data program display dataprogram and year
	 */
	private function step1_dataprog()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
                $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>		
		<table>
		<tr>
			<th>Data Program</th>
			<th>Year</th>
		</tr>
<tr><td><select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
<?php
		$sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
                                FROM CAPdata_programs WHERE program_type='data' ORDER BY name";
      		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
			<option value="<?php echo $row['id'] ?>"><?php echo $row['name']."(".$row['code'].")" ?></option>
			<?php
		}
?>
</select>
	</td><td>
<select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
<?php
		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
                                WHERE e.experiment_type_uid = et.experiment_type_uid
                                        AND et.experiment_type_name = 'phenotype'";
                if (!authenticate(array(USER_TYPE_PARTICIPANT,
                                        USER_TYPE_CURATOR,
                                        USER_TYPE_ADMINISTRATOR)))
                        $sql .= " and data_public_flag > 0";
                $sql .= " GROUP BY e.experiment_year ASC";
                $res = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($res)) {
                        ?>
                                <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
                        <?php
                }
                ?>
                                                </select>
                                        </td>
                                </tr>
</table>
<?php
	}	
	
	/**
	 * main entry point when there is a line selection in session variable
	 */
    private function type1_lines_trial_trait()
    {
		?>
		<div id="step11">
		<?php
	    $this->step1_lines();
		?>
	    </div></div>    
	    <div id="step2" style="float: left; margin-bottom: 1.5em;">
	    <?php
 	    $this->step2_lines();
	    ?>
	    </div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    <script type="text/javascript">
	      var mm = 99.9;
	      var mmaf = 0.01; 
          window.onload = load_markers_lines( mm, mmaf);
	    </script>
	    </div>
	     <?php 	
	}
	
	/**
	 * starting with lines display the selected lines
	 */
	private function step1_lines()
	{
		if (isset($_SESSION['selected_lines'])) {
			$selectedlines= $_SESSION['selected_lines'];
	        $count = count($_SESSION['selected_lines']);
		?>
	    <table id="phenotypeSelTab" class="tableclass1">
	    <tr>
	    <th>Lines</th>
	    </tr>
	    <tr><td>
	    <select name="lines" multiple="multiple" style="height: 12em;">
	    <?php
	    foreach($selectedlines as $uid) {
	      $sql = "SELECT line_record_name from line_records where line_record_uid = $uid";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res)
	      ?>
	      <option disabled="disabled" value="
	      <?php $uid ?>">
	      <?php echo $row['line_record_name'] ?>
	      </option>
	      <?php
	    }
	    ?>
	    </select>
	    </td>
	    </table>
	    <?php 
	    } else {
	    	echo "Please select lines before using this feature.<br>";
	        echo "<a href=";
	        echo $config['base_url'];
	        echo "pedigree/line_selection.php>Select Lines by Properties</a>";
	    }
	}
	
	/**
	 * starting with lines display trials
	 */
	private function step2_lines()
	{
	    if (isset($_SESSION['selected_lines'])) {
	        $selectedlines= $_SESSION['selected_lines'];
	        $count = count($_SESSION['selected_lines']);
		?>
	    <p>2.
	    <select name="select2">
	      <option value="trials">Trials</option>
	    </select></p>
	    <table id="linessel" class="tableclass1">
	    <tr>
	    <th>Trials</th>
	    </tr>
	    <tr><td>
	    <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_trial(this.options)">
	    <?php
	    $selectedlines= $_SESSION['selected_lines'];
	    $selectedlines = implode(',', $selectedlines);
	    $sql="SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name, e.experiment_year as year
	    FROM experiments as e, tht_base as tb, line_records as lr
	    WHERE
	    e.experiment_uid = tb.experiment_uid
	    AND lr.line_record_uid = tb.line_record_uid
	    AND e.experiment_type_uid = 1
	    AND lr.line_record_uid IN ($selectedlines)";
	    if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	    $sql .= " and data_public_flag > 0";
            $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
		$res = mysql_query($sql) or die(mysql_error());
                $last_year = NULL;
		while ($row = mysql_fetch_assoc($res))
		{
                  if ($last_year == NULL) {
                  ?>
                    <optgroup label="<?php echo $row['year'] ?>">
                  <?php
                    $last_year = $row['year'];
                  } else if ($row['year'] != $last_year) {
                  ?>
                  </optgroup>
                  <optgroup label="<?php echo $row['year'] ?>">
                  <?php
                    $last_year = $row['year'];
                  }
                 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		} 
	    ?>
            </optgroup>
	    </select></table>
	    <?php
	    } 
	}
	
	/**
	 * starting with lines display phenotype items
	 */
	private function step3_lines()
	{
	    $experiments = $_GET['e'];
	    if (isset($_GET['pi'])) {
	      if (preg_match("/\d/",$phen_item)) {
	         $_SESSION['phenotype'] = $phen_item;
	       } else {
	         unset($_SESSION['phenotype']);
	       }
	    }
		?>
	    <p>3.
	    <select name="select3">
	      <option value="phenotypes">Traits</option>
	    </select></p>
	    <table id="" class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
            <select id="traitsbx" name="traits" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_pheno(this.options)">
	    <?php
		$sql = "SELECT DISTINCT p.phenotype_uid AS id, phenotypes_name AS name from phenotypes as p, tht_base as tb, phenotype_data as pd
	        where pd.tht_base_uid = tb.tht_base_uid
                AND p.phenotype_uid = pd.phenotype_uid
	        AND tb.experiment_uid in ($experiments)";
		$res = mysql_query($sql) or die(mysql_error() . $sql);
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
	    ?>
	    </select></table>
	     <?php 		
	}
	
	/**
	 * starting with lines display marker data
	 */
	private function step4_lines() {
	 $experiments = $_GET['e'];
	 $saved_session = "";
	 $message2 = "";

	if (isset($_GET['pi'])) {
	  $phen_item = $_GET['pi'];
	  if (preg_match("/\d/",$phen_item)) {
	     $_SESSION['phenotype'] = $phen_item;
	  } else {
	     unset($_SESSION['phenotype']);
	  }
	}	
	if (isset($_SESSION['phenotype'])) {
	    $phenotype = $_SESSION['phenotype'];
	    $message2 = "download phenotype and genotype data";
	} else {
	    $phenotype = "";
	    $message2 = " download genotype data";
	}
	 if (isset($_SESSION['selected_lines'])) {
	     $countLines = count($_SESSION['selected_lines']);
	     $lines = $_SESSION['selected_lines'];
	     $selectedlines = implode(",", $_SESSION['selected_lines']);
	     if ($saved_session == "") {
	      $saved_session = "$countLines lines";
	     } else {
	      $saved_session = $saved_session . ", $countLines lines";
	     }
	 } else {
	     $countLines = 0;
	 }
	 if (isset($_SESSION['clicked_buttons'])) {
	    $tmp = count($_SESSION['clicked_buttons']);
	    $saved_session = $saved_session . ", $tmp markers";
	    $markers = $_SESSION['clicked_buttons']; 
	    $marker_str = implode(',',$markers);
	 } else {
	    $markers = "";
	    $marker_str = "";
	 }
	 
	 if ($saved_session != "") {
	  echo "current data selection = $saved_session";
	 }
	 
	 // initialize markers and flags if not already set
	 $max_missing = 99.9;//IN PERCENT
	 if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
	  $max_missing = $_GET['mm'];
	 if ($max_missing>100)
	  $max_missing = 100;
	 elseif ($max_missing<0)
	 $max_missing = 0;
	 $min_maf = 0.01;//IN PERCENT
	 if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
	  $min_maf = $_GET['mmaf'];
	 if ($min_maf>100)
	  $min_maf = 100;
	 elseif ($min_maf<0)
	  $min_maf = 0;
	 
	 /* $this->calculate_af($lines, $min_maf, $max_missing); */
         $traits_ary = explode(",",$phen_item);
         $count = count($traits_ary);
         echo ", $count traits";
	 
	 if ($saved_session != "") {
	     if ($countLines == 0) {
	       echo "Choose one or more lines before using a saved selection. ";
	       echo "<a href=";
	       echo $config['base_url'];
	       echo "pedigree/line_selection.php> Select lines</a><br>";
	     } else {
	       ?>
               <table> <tr> <td COLSPAN="3">
               <input type="hidden" name="subset" id="subset" value="yes" /><br>
               <input type="button" value="Save current selection" onclick="javascript: load_title('save');"/>
               </td> </tr> </table>
	       <?php    
	     }
	  }
	}
	
	/**
	 * starting with location display all locations
	 */
	private function step1_locations() {
	 ?>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Location</th>
	 </tr>
	 <tr><td>
	 <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript:update_locations(this.options)">
	 <?php
	 $sql = "SELECT distinct location as name from phenotype_experiment_info where location is not NULL order by location";
	 $res = mysql_query($sql) or die(mysql_error());
	 while ($row = mysql_fetch_assoc($res)) {
	   ?>
	   <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
	   <?php 
	 }
	 ?>
	 </select>
	 </td>
	 </table>
	 <?php
	}
	
	/**
	 * starting with location display years
	 */
	private function step2_locations() {
	 $locations = $_GET['loc'];
	 $locations = stripslashes($locations);
	 ?>
	 <p><select>
	 <option>Year</option>
	 </select>
	 </p>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Year</th>
	 </tr>
	 <tr><td>
	 <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript:update_years(this.options)">
	 <?php
	 $sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et, phenotype_experiment_info AS p_e
	 WHERE e.experiment_uid = p_e.experiment_uid
	 AND e.experiment_type_uid = et.experiment_type_uid
	 AND et.experiment_type_name = 'phenotype'
	 AND p_e.location IN ($locations)
	 GROUP BY e.experiment_year DESC";
	 $res = mysql_query($sql) or die(mysql_error());
	 while ($row = mysql_fetch_assoc($res)) {
	   ?>
	   <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
	   <?php
	 }
	 ?>
	 </select>
	 </td>
	 </table>
	 <?php
	}
	
	/**
	 * starting with location display experiments
	 */
	private function step3_locations() {
	 $locations = $_GET['loc']; //"'" . implode("','", $_GET['loc']) . "'";
	 $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
	 $locations = stripslashes($locations);
	 ?>
	 <p>3.
	 <select name="select1">
	 <option value="BreedingProgram">Trials</option>
	 </select></p>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Trials</th>
	 </tr>
	 <tr><td>
	 <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_experiments(this.options)">
	 <?php
	 $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
	 FROM experiments AS e, experiment_types AS e_t, phenotype_experiment_info AS p_e
	 WHERE e.experiment_uid = p_e.experiment_uid
	 AND p_e.location IN ($locations)
	 AND e.experiment_year IN ($years)
	 AND e.experiment_type_uid = e_t.experiment_type_uid
	 AND e_t.experiment_type_name = 'phenotype'";
	 if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	 $sql .= " and data_public_flag > 0";
	 $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
	 $res = mysql_query($sql) or die(mysql_error());
         $last_year = NULL;
	 while ($row = mysql_fetch_assoc($res)) {
           if ($last_year == NULL) {
           ?>
             <optgroup label="<?php echo $row['year'] ?>">
           <?php
             $last_year = $row['year'];
           } else if ($row['year'] != $last_year) {
           ?>
             </optgroup>
             <optgroup label="<?php echo $row['year'] ?>">
           <?php
             $last_year = $row['year'];
           }
           ?>
	   <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
	   <?php
	 }
	 ?>
         </optgroup>
	 </select>
	 </td>
	 </table>
	 <?php
	}
	
	/**
	 * starting with locations display marker information
	 */
	private function step5_locations() {
	 // parse url
	 $experiments = $_GET['exps'];
	 $phen_item = $_GET['pi'];
	 $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
     //$_SESSION['phenotype'] = $phen_item;
	 
	 /**
	  * Use currently selected lines?
	  */
	 if (count($_SESSION['selected_lines']) > 0) {
	   $sub_ckd = ""; $all_ckd = "checked";
	 } else {
	   $sub_ckd = "disabled"; $all_ckd = "checked";
	 }
	 if ($subset == "yes") {
	   $sub_ckd = "yes"; $yes_ckd = "checked";
	 } elseif ($subset == "no") {
	   $sub_ckd = ""; $all_ckd = "checked";
	 } elseif ($subset == "comb") {
	   $sub_ckd = ""; $cmb_ckd = "checked";
	 }
	 ?>
	 <p>5.<select name="select1">
	 <option value="BreedingProgram">Lines</option>
	 </select></p>
	 
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Lines</th>
	 </tr>
	 <tr><td>
	 <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
	 <?php
	 //if (count($_SESSION['selected_lines']) > 0) {
	 if ($sub_ckd == "checked") {
	 	$selected_lines = $_SESSION['selected_lines'];
	 	foreach ($selected_lines as $line) {
	 		$sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	 		$res = mysql_query($sql) or die(mysql_error());
	 		$row = mysql_fetch_assoc($res);
	 		?>
	 		<option selected value="<?php echo $row['id'] ?>">
	        <?php echo $row['name'] ?>
	        </option>
	        <?php
	 	}
	 } elseif ($cmb_ckd == "checked") {
	   $lines_list = array();
	   $lines_new = "";
	   $selected_lines = $_SESSION['selected_lines'];
	   foreach ($selected_lines as $line) {
	     $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	     $res = mysql_query($sql) or die(mysql_error());
	     $row = mysql_fetch_assoc($res);
	     $temp = $row['id'];
	     $lines_list[$temp] = 1;
	     ?>
	    <option selected value="<?php echo $row['id'] ?>">
	    <?php echo $row['name'] ?>
	    </option>
	    <?php
	   }
	   $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   WHERE
	   pd.tht_base_uid = tb.tht_base_uid
	   AND p.phenotype_uid = pd.phenotype_uid
	   AND lr.line_record_uid = tb.line_record_uid
	   AND pd.phenotype_uid IN ($phen_item)
	   AND tb.experiment_uid IN ($experiments)
	   ORDER BY lr.line_record_name";
	   $res = mysql_query($sql) or die(mysql_error());
	   while ($row = mysql_fetch_assoc($res))
	   {
	      $temp1 = $row['name'];
	      $temp2 = $row['id'];
	      if (isset($lines_list[$temp2])) {
	      } else {
	        if ($lines_new == "") {
	           $lines_new = $temp1;
	           ?>
	           <option disabled="disabled">--added--
	           </option>
	           <?php 
	        }
	        ?>
	        <option selected value="<?php echo $row['id'] ?>">
	        <?php echo $temp1 ?>
	        </option>
	        <?php
	      }
	   }
	 } else {
	   $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   WHERE
	   pd.tht_base_uid = tb.tht_base_uid
	   AND p.phenotype_uid = pd.phenotype_uid
	   AND lr.line_record_uid = tb.line_record_uid
	   AND pd.phenotype_uid IN ($phen_item)
	   AND tb.experiment_uid IN ($experiments)
	   ORDER BY lr.line_record_name";
	   //$_SESSION['selected_lines'] = array(); // Empty the session array.
	   //$lines = array();
	   $res = mysql_query($sql) or die(mysql_error());
	   while ($row = mysql_fetch_assoc($res))
	   {
	   //array_push($_SESSION['selected_lines'],$row['id']);
	   //array_push($lines,$row['id']);
	   ?>
	   <option selected value="<?php echo $row['id'] ?>">
	   <?php echo $row['name'] ?>
	   </option>
	   <?php
	   }
	 }
	 ?>
	 </select>
	 </table>
	 <?php 
	 if (count($_SESSION['selected_lines']) > 0) {
	   ?>
           Combine with currently selected lines:<br>
	   <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Replace</b><br>
	   <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Add (OR)<br>
           <input type="radio" name="subset" id="subset" value="yes" <?php echo "$yes_ckd"; ?> onclick="javascript: update_phenotype_linesb(this.value)">Intersect (AND)<br>
	   <?php
	 } 
	}
	
	/**
	 * starting with breeding programs display marker information
	 */
	private function step5_programs() {
	  $experiments = $_GET['exps'];
	  $CAPdataprogram = $_GET['bp'];
	  $years = $_GET['yrs'];
	  $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
	 
	  /** Use currently selected lines? */
	  if (count($_SESSION['selected_lines']) > 0) {
	    $sub_ckd = ""; $all_ckd = "checked";
	  } else {
	    $sub_ckd = "disabled"; $all_ckd = "checked";
	  }
	  if ($subset == "yes") {
	    $sub_ckd = "yes"; $yes_ckd = "checked";
	  } elseif ($subset == "no") {
	    $sub_ckd = ""; $all_ckd = "checked";
	  } elseif ($subset == "comb") {
	    $sub_ckd = ""; $cmb_ckd = "checked";
	  }
	  ?>
	  <p>5.<select name="select1">
	  <option value="BreedingProgram">Lines</option>
	  </select></p>
	  
	  <table id="phenotypeSelTab">
	  <tr>
	  <?php 
	  $sql_option = "";
	  if (preg_match("/\d/",$experiments)) {
	  	$sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
	  }
	  if (preg_match("/\d/",$datasets)) {
	  	$sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
	  }
	  $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
	  FROM line_records, tht_base
	  WHERE line_records.line_record_uid=tht_base.line_record_uid
	  $sql_option";
	  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	  while($row = mysql_fetch_array($res)) {
	  	$count++;
	  }
          if (isset($_SESSION['selected_lines'])) {
            ?>
            <td>Lines found: <?php
            echo $count;
            ?>
            <td><td>Current selection: <?php echo count($_SESSION['selected_lines']); ?></td><td></td>
            <?php
          } else {
          ?>
            <th>Lines</th>
          <?php
          }
          ?>
	  </tr>
	  <tr><td>
          <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">;
            <?php
            $sql_option = "";
            if (preg_match("/\d/",$experiments)) {
              $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
            }
            if (preg_match("/\d/",$datasets)) {
              $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
            }
            $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
            FROM line_records, tht_base
            WHERE line_records.line_record_uid=tht_base.line_record_uid
            $sql_option";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            while($row = mysql_fetch_array($res)) {
              ?>
              <option selected value="<?php echo $row['id'] ?>">
              <?php echo $row['name'] ?>
              </option>
              <?php
            }
            ?>
          </select>
          <?php

	  if (isset($_SESSION['selected_lines'])) {
            ?>
            <td>
            Combine with currently<br>selected lines:<br>
            <input type="radio" name="subset" id="subset" value="no" <?php echo $all_ckd; ?> onclick="javascript: update_phenotype_linesb(this.value)">Replace</b><br>
            <input type="radio" name="subset" id="subset" value="comb" <?php echo $cmb_ckd; ?> onclick="javascript: update_phenotype_linesb(this.value)">Add (OR)<br>
            <input type="radio" name="subset" id="subset" value="yes" <?php echo $yes_ckd; ?> onclick="javascript: update_phenotype_linesb(this.value)">Intersect (AND)<br>
            <td>
	    <select name="lines" multiple="multiple" style="height: 12em;">
	    <?php 
	    $lines_list = array();
	    $lines_new = "";
	    $selected_lines = $_SESSION['selected_lines'];
	    foreach ($selected_lines as $line) {
	      $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res);
	      $temp = $row['id'];
	      $lines_list[$temp] = 1;
	      ?>
	      <option disabled="disabled" value="<?php echo $row['id'] ?>">
	      <?php echo $row['name'] ?>
	      </option>
	      <?php
	    }
	    ?>
	    </select>
	  <?php
	  }
	  ?>
	  </table>
	  <?php 
	}

	/**
	 * allow entry of lines, this function is not used at this time
	 */
	private function enter_lines()
	{
		if($_SERVER['REQUEST_METHOD'] == "GET")
  // Store what the user's previous selections were so we can
  // redisplay them as the page is redrawn.
 		{
		    $name = $_GET['LineSearchInput'];
		    echo "$names<br>";
		}
		?>
		<form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="GET">
                <input type="hidden" name="function" value="enterlines">

      		<b>Name</b> <br/><br/>
      		<textarea name="LineSearchInput" rows="3" cols="20" style="height: 6em;"><?php $nm = explode('\r\n', $name); foreach ($nm as $n) echo $n."\n"; ?></textarea>
      		<br> Eg: Cayuga, Doyce<br>
      		Synonyms will be translated.
		<input type="submit" value=Search>
		</form>
		<?php
		if (isset($_GET['LineSearchInput'])) {
			$linenames = $_POST['LineSearchInput'];
			echo "made it here\n";
			if (strlen($linenames) != 0)
    
        if (strpos($linenames, ',') > 0 ) {
                        $linenames = str_replace(", ",",", $linenames);
                        $lineList = explode(',',$linenames);
                } elseif (preg_match("/\t/", $linenames)) {
                        $lineList = explode("\t",$linenames);
                } else {
                        $lineList = explode('\r\n',$linenames);
                }

        $items = implode("','", $lineList);
        $mStatment = "SELECT distinct (lr.line_record_name) FROM line_records lr left join line_synonyms ls on ls.line_record_uid = lr.line_record_uid where ls.line_synonym_name in ('" .$items. "') or lr.line_record_name in ('". $items. "');";

        $res = mysql_query($mStatment) or die(mysql_error());

        if (mysql_num_rows($res) != 0) {
        while($myRow = mysql_fetch_assoc($res)) {
          array_push ($lineArr,$myRow['line_record_name']);
        }
        // Generate the translated line names
        $linenames =  implode("','", $lineArr);
      } else {
        $linenames = '';
      }
	}
	}
	
	/**
	 * display a list of experiments
	 */
	private function type1_experiments()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
		$years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
<p>3. 
<select>
  <option>Trials</option>
</select></p>
<div>

<table>
	<tr><th>Trials</th></tr>
	<tr><td>
		<select name="experiments" multiple="multiple"
		  style="height: 12em" onchange="javascript: update_experiments(this.options)">
<?php
//	List phenotype experiments associated with a list of breeding programs and years selected by the user,
//  needs to used datasets/experiments 
//	linking table.

		$sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
				FROM experiments AS e, datasets AS ds, datasets_experiments AS d_e, experiment_types AS e_t
				WHERE e.experiment_uid = d_e.experiment_uid
				AND d_e.datasets_uid = ds.datasets_uid
				AND ds.breeding_year IN ($years)
				AND ds.CAPdata_programs_uid IN ($CAPdata_programs)
				AND e.experiment_type_uid = e_t.experiment_type_uid
				AND e_t.experiment_type_name = 'phenotype'";
		        if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
		        $sql .= " and data_public_flag > 0";
				$sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
				
		$res = mysql_query($sql) or die(mysql_error());
		$last_year = NULL;
		while ($row = mysql_fetch_assoc($res)) {			
			if ($last_year == NULL) {
?>
			<optgroup label="<?php echo $row['year'] ?>">
<?php
				$last_year = $row['year'];
			} else if ($row['year'] != $last_year) {
?>
			</optgroup>
			<optgroup label="<?php echo $row['year'] ?>">
<?php
				$last_year = $row['year'];
			}
?>
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
		}
?>
			</optgroup>
		</select>
	</td></tr>
</table>
</div>
<?php
	}
	
	/**
	 * display traits given a list of experiments
	 */
	private function type1_traits()
	{
		$experiments = $_GET['exps'];
		
		if (empty($experiments))
		{
			echo "
				4. <select><option>Traits</option></select>
				<div>
					<p><em>No Trials Selected</em></p>
				</div>";
		}
		else
		{
?>
<p>4. 
<select><option>Traits</option></select></p>
<div>
<?php
// List all traits associated with a list of experiments


			$sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
					FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
					WHERE pd.tht_base_uid = t.tht_base_uid
					AND p.phenotype_uid = pd.phenotype_uid
					AND t.experiment_uid IN ($experiments)
					GROUP BY p.phenotype_uid";

			$res = mysql_query($sql) or die(mysql_error());
			if (mysql_num_rows($res) >= 1)
			{
?>
<table>
	<tr><th>Trait</th></tr>
	<tr><td>
		<select id="traitsbx" name="traits" multiple="multiple" style="height: 12em" onchange="javascript: update_phenotype_items(this.options)">
<?php
				while ($row = mysql_fetch_assoc($res))
				{
?>
			<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
				}
?>
		</select>
	</td></tr>
</table>
<?php
			}
			else
			{
?>
		<p style="font-weight: bold;">No Data</p>
<?php
			}
?>
</div>
<?php
		}
	}

	/**
	 * displays key marker data for the selected breeding programs
	 */
	private function type1_markers()
	{
		// parse url
        $experiments = $_GET['exps'];
		$CAPdataprogram = $_GET['bp'];
		$phen_item = $_GET['t'];
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
                $traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		
		if (empty($_GET['lines'])) {
		if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
		  $lines = $_SESSION['selected_lines'];
		  $lines_str = implode(",", $lines);
		  $count = count($_SESSION['selected_lines']);
		} else {
		  $sql_option = "";
		  $lines = array();
		  if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
		    $selectedlines = implode(",", $_SESSION['selected_lines']);
		    $sql_option = " AND tb.line_record_uid IN ($selectedlines)";
		  }
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tb.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$phen_item)) {
		     $sql_option .= "AND pd.phenotype_uid IN ($phen_item)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tb.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT tb.line_record_uid FROM tht_base as tb, phenotype_data as pd
		  WHERE pd.tht_base_uid = tb.tht_base_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
		    $lines[] = $row['line_record_uid'];
		  }
		  //echo "$sql<br>\n";
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		}
		//overide these setting is radio button checked
		if ($subset == "no") {
		  $sql_option = "";
		  $lines = array();
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
		  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
		    $lines[] = $row['line_record_uid'];
		  }
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		} elseif ($subset == "comb") {
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
		  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
                    $line_uid = $row['line_record_uid'];
                    if (!in_array($line_uid, $lines)) {
		      $lines[] = $row['line_record_uid'];
                    }
		  }
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		} elseif ($subset == "yes") {
                  if (preg_match("/\d/",$experiments)) {
                    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
                  }
                  if (preg_match("/\d/",$datasets)) {
                    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
                  }
                  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
                  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
                  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                  while($row = mysql_fetch_array($res)) {
                    $temp[] = $row['line_record_uid'];
                  }
                  $lines = array_intersect($lines,$temp);
                  $lines_str = implode(",", $lines);
                  $count = count($lines);
                }
		} else {
	      $lines_str = $_GET['lines'];
	      $lines = explode(',', $lines_str);
	      $count = count($lines);
		}
                echo "<div>";
		echo "current data selection = $count lines";
		    		
		// initialize markers and flags if not already set
        $max_missing = 99.9;//IN PERCENT
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
		
		/* $this->calculate_af($lines, $min_maf, $max_missing);  */
                $traits_ary = explode(",",$traits); 
                $count = count($traits_ary);
                echo ", $count traits";
		
		?>
		<table> <tr> <td COLSPAN="3">
		<input type="hidden" name="subset" id="subset" value="yes" /><br>
                <input type="button" value="Save current selection" onclick="javascript: load_title('save');"/>
		</td> </tr> </table>
		<?php
		
		?></div><?php
			
	}
	
	/**
	 * displays key marker data when given a set of experiments and phenotypes
	 */
	private function type2_markers()
	{
	 // parse url
	 $experiments = $_GET['exps'];
	 $phen_item = $_GET['pi'];
	 $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
	 
	 if (empty($_GET['lines'])) {
	   if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
	   	 $lines = $_SESSION['selected_lines'];
	     $lines_str = implode(",", $lines);
	     $count = count($_SESSION['selected_lines']);
	   } else {
	   	 $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   	 WHERE
	   	 pd.tht_base_uid = tb.tht_base_uid
	   	 AND p.phenotype_uid = pd.phenotype_uid
	   	 AND lr.line_record_uid = tb.line_record_uid
	   	 AND pd.phenotype_uid IN ($phen_item)
	   	 AND tb.experiment_uid IN ($experiments)
	   	 ORDER BY lr.line_record_name";
	   	 $lines = array();
	   	 $res = mysql_query($sql) or die(mysql_error() . $sql);
	   	 while ($row = mysql_fetch_assoc($res))
	   	 {
	   		array_push($lines,$row['id']);
	     }
	     $lines_str = implode(",", $lines);
	     $count = count($lines);
	     //$_SESSION['selected_lines'] = $lines;
	   }
	   //overide these setting is radio button checked
	   if ($subset == "no") {
	   	 $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   	 WHERE
	   	 pd.tht_base_uid = tb.tht_base_uid
	   	 AND p.phenotype_uid = pd.phenotype_uid
	   	 AND lr.line_record_uid = tb.line_record_uid
	   	 AND pd.phenotype_uid IN ($phen_item)
	   	 AND tb.experiment_uid IN ($experiments)
	   	 ORDER BY lr.line_record_name";
	   	 $lines = array();
	   	 $res = mysql_query($sql) or die(mysql_error() . $sql);
	   	 while ($row = mysql_fetch_assoc($res))
	   	 {
	   	 	array_push($lines,$row['id']);
	   	 }
	   	 $lines_str = implode(",", $lines);
	   	 $count = count($lines);
	   } elseif ($subset == "comb") {
	     $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	     FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	     WHERE
	     pd.tht_base_uid = tb.tht_base_uid
	     AND p.phenotype_uid = pd.phenotype_uid
	     AND lr.line_record_uid = tb.line_record_uid
	     AND pd.phenotype_uid IN ($phen_item)
	     AND tb.experiment_uid IN ($experiments)
	     ORDER BY lr.line_record_name";
	     $res = mysql_query($sql) or die(mysql_error() . $sql);
	     while ($row = mysql_fetch_assoc($res))
	     {
               $line_uid = $row['id'];
               if (!in_array($line_uid, $lines)) {
                 $lines[] = $row['line_record_uid'];
               }
	     }
	     $lines_str = implode(",", $lines);
	     $count = count($lines);
	   } elseif ($subset = "yes") {
             $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
             FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
             WHERE
             pd.tht_base_uid = tb.tht_base_uid
             AND p.phenotype_uid = pd.phenotype_uid
             AND lr.line_record_uid = tb.line_record_uid
             AND pd.phenotype_uid IN ($phen_item)
             AND tb.experiment_uid IN ($experiments)
             ORDER BY lr.line_record_name";
             $res = mysql_query($sql) or die(mysql_error() . $sql);
             while ($row = mysql_fetch_assoc($res))
             {
               $temp[] = $row['id'];
             }
             $lines = array_intersect($lines, $temp);
             $lines_str = implode(",", $lines);
             $count = count($lines);
           }
	 } else {
	   $lines_str = $_GET['lines'];
	   $lines = explode(',', $lines_str);
	   $count = count($lines);
	   //$_SESSION['selected_lines'] = $lines;
	 }
	 echo "current data selection = $count lines";
	
	 // initialize markers and flags if not already set
	 $max_missing = 99.9;//IN PERCENT
	 if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
	 $max_missing = $_GET['mm'];
	 if ($max_missing>100)
	   $max_missing = 100;
	 elseif ($max_missing<0)
	  $max_missing = 0;
	 $min_maf = 0.01;//IN PERCENT
	 if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
	   $min_maf = $_GET['mmaf'];
	 if ($min_maf>100)
	   $min_maf = 100;
	 elseif ($min_maf<0)
	   $min_maf = 0;
	
	/* $this->calculate_af($lines, $min_maf, $max_missing); */
        $traits_ary = explode(",",$phen_item);
        $count = count($traits_ary);
        echo ", $count traits";
	
	?>
	<table> <tr> <td COLSPAN="3">
	<input type="hidden" name="subset" id="subset" value="yes" /><br>
        <input type="button" value="Save current selection" onclick="javascript: load_title('save');"/>
	</td> </tr> </table>
	<?php
	
	?></div><?php
	
	}
	
}// end class
