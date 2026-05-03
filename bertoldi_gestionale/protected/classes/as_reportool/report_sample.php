<?PHP
/**
* sample for demonstrating as_reportool.php functionality
* @author Alexander Selifonov <as-works@narod.ru>
* @link http://www.selifan.ru
* @license http://www.gnu.org/copyleft/gpl.html
* modified 26.11.2008
*/
require_once('as_reportool.php');
#  draw HTML header code...
?>
<html><head>
</head>
<body>

<?PHP
$as_dbengine->Connect('localhost','','','mydb'); 
# Your MySQL host, login, password and database name.

$rep = new CReporTool();

$rep->SetQuery("SELECT c.categoryid, b.animalid, a.nickname,a.gender,a.birth,a.weight FROM big_zoo a, animals b, animal_categories c
   WHERE a.animalid=b.animalid AND b.category=c.categoryid ORDER BY c.categoryid, b.animalid");

$rep->AddGroupingField('categoryid','GetAnymalCategoryName','Animal category ','Totals for category %name%');

$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');

$rep->AddField('nickname','Nick');

$rep->AddField('gender','Gender',0,'DecodeGender'); // DecodeGender() will show 'male' for 'm' and female for 'f' value.
$rep->AddField('birth','Birth date',0,'DateToChar'); // your function DateToChar converts DATE value to be more readable
$rep->AddField('weight','Weight, kg',1,'','i'); // this field is summable and will be printed right-aligned and number_format()ted

$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

# $rep->SetNumberDelimiters(',',' '); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('Summary for all animals (%rowcount%) ');
$rep->DrawReport('Report: All animals in zoo');

function GetAnymalCategoryName($id) {
  global $as_dbengine;
  return $as_dbengine->GetQueryResult('animal_categories','categoryname',"categoryid=$id");
}

function GetAnymalClassName($id) {
  global $as_dbengine;
  return $as_dbengine->GetQueryResult('animals','animalname',"animalid=$id");
}
function DecodeGender($par) {
  return ($par=='m')? 'male':'female';
}
function DateToChar($par) { # return mm/dd/yyyy from MySQL date format YYYY-MM-DD
  $dt = explode('-',$par);
  return $dt[1].'/'.$dt[2].'/'.$dt[0];
}
?>

</body></html>