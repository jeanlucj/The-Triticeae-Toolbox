<?php
/**
 * format-fasta.php
 * create fasta file for all the markers in the database
 *
 * PHP version 5
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/format-fasta.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
connect();

//needed for mac compatibility
ini_set('auto_detect_line_endings',true);

$sql = "select value from settings where name = \"database\"";
$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
if ($row = mysql_fetch_array($res)) {
  $database = $row[0];
}
  
//create file of SNP fasta
$tmp = $config['root_dir'] . "viroblast/db/nucleotide/wheat-markers";
if ($fh = fopen("$tmp","w")) {
  echo "opened output file<br>\n";
} else {
  die("can not open output $tmp\n");
}
$count = 0;
$sql = "select marker_uid, marker_name, sequence from markers where sequence is not NULL";
$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
while ($row = mysql_fetch_array($res)) {
  $uid = $row[0];
  $name = $row[1];
  $seq = $row[2];
  $pattern = "/[A-Za-z0-9-_\.]+/";
  if (preg_match($pattern,$name,$match)) {
    $name = $match[0];
  } 
  $pattern = "/[A-Za-z]/";
  if (preg_match($pattern, $seq)) {
      $pattern = "/\[A\/G\]/"; $replace = "R";
      $seq = preg_replace($pattern,$replace,$seq);
      $pattern = "/\[T\/C\]/"; $replace = "Y";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[A\/C\]/"; $replace = "M";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[T\/G\]/"; $replace = "K";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[C\/T\]/"; $replace = "Y";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[G\/T\]/"; $replace = "K";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[C\/G\]/"; $replace = "S";
  $seq = preg_replace($pattern,$replace,$seq);
  $pattern = "/\[A\/T\]/"; $replace = "W";
  $seq = preg_replace($pattern,$replace,$seq);
  fwrite($fh,">gnl|$database|$name\n$seq\n");
  $count++;
  } else {
      echo "skip $name<br>\n";
  }
}

echo "$count markers found<br>\n";
$out = $config['root_dir'] . "viroblast/db/nucleotide";
chdir($out);
$cmd = $config['root_dir'] . "viroblast/blastplus/bin/formatdb -i wheat-markers -p F -o T";
//echo "$cmd<br>\n";
exec($cmd);