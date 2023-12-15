<?php
//
// This file must be included and $invoice array() must exist with at least the lname, produ and price keys.
//
// $invoice param :
//	(templ) template,		(fname) first name,		(lname)* last name,		(email) email,
//	(adres) adresse,		(produ)* product,		(ref) product ref,		(date) date,
//	(price)* price TTC,		(ship) Shipping TTC,	(tax) tax (included),	(curr) currency,
//	(metho) payment type,	(file) output,			(lang) lang (fr,en,es)
// 
//
if(empty($invoice['lname']) || empty($invoice['produ']) || empty($invoice['price'])) return;
if(empty($langPlug)) {
	if(empty($lang)) $lang = (!empty($invoice['lang'])?$invoice['lang']:'en');
	include('lang/lang.php');
}
//
// 1. Sanitize and set $invoice
$in = array('templ','fname','lname','email','adres','produ','date','price','ship','tax','curr','metho','file','output','lang');
$n = array('"','(',')','{','}','[',']','<','>','|','+','=','?',';','`','*');
foreach($in as $i) {
	$invoice[$i] = (!empty($invoice[$i])?strip_tags($invoice[$i]):'');
	$invoice[$i] = preg_replace("/\s+/", " ",$invoice[$i]); // multiple spaces & lines break
	$invoice[$i] = mb_convert_encoding(str_replace($n,"",$invoice[$i]), 'ISO-8859-1', 'UTF-8');
}
//
// 2. Default
include('fpdf/fpdf.php');
if(empty($invoice['curr']) || $invoice['curr']=='EUR') $invoice['curr'] = chr(128); // EURO
else if($invoice['curr']=='USD' || $invoice['curr']=='CAD') $curr = '$';
else if($invoice['curr']=='GBP') $invoice['curr'] = '£';
if(empty($invoice['date'])) $invoice['date'] = date('j M Y');
if(empty($invoice['file'])) $invoice['file'] = 'invoice.pdf';
//
// 3. Load settings
$templ = array('logo'=>'','addr'=>'','comp'=>'','foot'=>'');
if(file_exists(dirname(dirname(dirname(__FILE__))).'/data/invoice.json')) {
	$q = file_get_contents(dirname(dirname(dirname(__FILE__))).'/data/invoice.json');
	$templ = json_decode($q,true);
}
//
// 4. Init PDF
$pdf = new FPDF('P','mm','A4'); // portrait (P / L) - milimetre - A4
$pdf->AddPage();
$pdf->SetMargins(20, 20); // left, top (mm)
$pdf->SetFillColor(224,235,255);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(70,82,103);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',18);
// 5. Business
// Logo left-top of the page
$logocell = 0;
if(!empty($templ['logo'])) {
	$size = getimagesize($templ['logo']);
	$logocell = intval($size[0]/4.58); // Logo Width / 4.58
	$pdf->Image($templ['logo'],20,16,30);
}
// Business address right to the logo
if(!empty($templ['addr'])) {
	$pdf->SetFont('','B',10);
	$pdf->Ln(3);
	$addr = explode("\r\n",$templ['addr']);
	foreach($addr as $a) {
		if($a) {
			$pdf->Cell($logocell);
			$pdf->Cell(130,10,mb_convert_encoding($a, 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$pdf->SetFont('','');
		}
		$pdf->Ln(4);
	}
}
// 6. Invoice content
// Title
$pdf->SetFont('','BU',24);
$pdf->Ln(12);
$pdf->Cell(170,10,Tiso_("Invoice"),'',0,'C');
$pdf->Ln(20);
// Order Details
$refinv = substr(($invoice['lname']?$invoice['lname']:($invoice['email']?strtok($invoice['email'],'@'):(preg_replace("/[^a-z0-9_,-]/","", strtolower($invoice['product']))))),0,6).time();
$pdf->SetFont('','B',14);
$pdf->Cell(170,10,Tiso_("Order"));
$pdf->Ln(10);
$pdf->SetFont('','B',10);
$pdf->Cell(20,6,Tiso_("Ref"),1,0,'L',true);
$pdf->SetFont('');
$pdf->Cell(80,6,$refinv,1,0,'L',false); // $a(id)
$pdf->Ln();
$pdf->SetFont('','B');
$pdf->Cell(20,6,Tiso_("Date"),1,0,'L',true);
$pdf->SetFont('');
$pdf->Cell(80,6,$invoice['date'],1,0,'L',false); // a(time)
$pdf->Ln();
$pdf->SetFont('','B');
$pdf->Cell(20,6,Tiso_("Name"),1,0,'L',true);
$pdf->SetFont('');
$pdf->Cell(80,6,ucfirst($invoice['fname']).($invoice['fname']?' ':'').ucfirst($invoice['lname']),1,0,'L',false);
if($invoice['adres']) {
	$pdf->Ln(); $pdf->SetFont('','B');
	$pdf->Cell(20,6,Tiso_("Address"),1,0,'L',true);
	$pdf->SetFont('');
	$pdf->Cell(80,6,$invoice['adres'],1,0,'L',false);
}
if($invoice['email']) {
	$pdf->Ln();
	$pdf->SetFont('','B');
	$pdf->Cell(20,6,Tiso_("Mail"),1,0,'L',true);
	$pdf->SetFont('');
	$pdf->Cell(80,6,$invoice['email'],1,0,'L',false);
}
$pdf->Ln();
$pdf->SetFont('','B');
$pdf->Cell(20,6,Tiso_("Payment"),1,0,'L',true);
$pdf->SetFont('');
$pdf->Cell(80,6,ucfirst($invoice['metho']?$invoice['metho']:'OK'),1,0,'L',false);
// Order content
$t1 = array(Tiso_("Name"), Tiso_("Ref"), Tiso_("Price"), 'Nb', Tiso_("Tax"), Tiso_("Total"));
$pdf->Ln(20);
$pdf->SetFont('','B',14);
$pdf->Cell(170,10,Tiso_("Order Details"));
$pdf->Ln(10);
$pdf->SetFont('','B',10);
$w = array(65, 30, 20, 10, 20, 25); // 170
for($i=0;$i<count($t1);$i++) $pdf->Cell($w[$i],6,$t1[$i],1,0,'C',true);
$pdf->Ln();
$pdf->SetFont('');
$pht = $invoice['price']; $tax = '';
if($invoice['tax']) {
	$pht = number_format(floatval($invoice['price'])*(1-floatval($invoice['tax'])), 2, '.', '');
	$tax = number_format(floatval($invoice['price'])*floatval($invoice['tax']), 2, '.', '');
}
$total = number_format(floatval($invoice['price'])+floatval($invoice['ship']), 2, '.', '');
	$pdf->Cell($w[0],6,$invoice['produ'],'LR',0,'L',false);
	$pdf->Cell($w[1],6,$invoice['ref'],'LR',0,'C',false);
	$pdf->Cell($w[2],6,$pht.' '.$invoice['curr'],'LR',0,'C',false);
	$pdf->Cell($w[3],6,'1','LR',0,'C',false);
	$pdf->Cell($w[4],6,($tax?$tax.' '.$invoice['curr']:'/'),'LR',0,'C',false);
	$pdf->Cell($w[5],6,$invoice['price'].' '.$invoice['curr'],'LR',0,'R',false);
	$pdf->Ln();
$pdf->Cell(array_sum($w),0,'','T');
if($invoice['ship']) {
	$pdf->Ln();
	$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4],6,Tiso_("Shipping cost"),'',0,'R');
	$pdf->Cell($w[5],6,$invoice['ship'].' '.$invoice['curr'],'LRB',0,'R');
}
$pdf->Ln();
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4],6,Tiso_("Total").(!$invoice['tax']?' Net':''),'',0,'R');
$pdf->SetFont('','B');
$pdf->Cell($w[5],6,$total.' '.$invoice['curr'],'LRB',0,'R');
// Further explanations
if(!empty($templ['comp'])) {
	$pdf->SetFont('','I',9);
	$pdf->Ln(3);
	$pdf->Cell(170,10,mb_convert_encoding($templ['comp'], 'ISO-8859-1', 'UTF-8'),0,0,'L');
}
// Shipping
if($invoice['ship']) {
	$pdf->Ln(20); $pdf->SetFont('','B',14);
	$pdf->Cell(170,10,Tiso_("Shipping address"));
	$pdf->Ln(10); $pdf->SetFont('','B',10);
	$pdf->Cell(30,6,Tiso_("Name"),1,0,'L',true);
	$pdf->SetFont('');
	$pdf->Cell(140,6,$invoice['fname'].($invoice['fname']?' ':'').$invoice['lname'],1,0,'L',false);
	$pdf->Ln(); $pdf->SetFont('','B');
	$pdf->Cell(30,6,Tiso_("Address"),1,0,'L',true);
	$pdf->SetFont('');
	$pdf->Cell(140,6,$invoice['adres'],1,0,'L',false);
	$pdf->Ln(); $pdf->SetFont('','B');
	$pdf->Cell(30,6,Tiso_("Mail"),1,0,'L',true);
	$pdf->SetFont('');
	$pdf->Cell(140,6,$invoice['email'],1,0,'L',false);
	$pdf->Ln();
}
// 7. Date top-right of the page
$pdf->SetFont('','',10);
$pdf->SetY(18);
$pdf->Cell(120);
$pdf->Cell(50,10,$invoice['date'],0,0,'R');
// 8. Footer
if(!empty($templ['foot'])) {
	$pdf->SetY(-30);
	$pdf->SetFont('','',10); $pdf->SetTextColor(60);
	$pdf->Cell(170,6,mb_convert_encoding($templ['foot'], 'ISO-8859-1', 'UTF-8'),'',0,'C');
}
// 9. Output PDF
if($invoice['output']=='S') { // Save File to /files/invoice/
	$o = $pdf->Output('S');
	if(file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))).'/files/invoice/'.$invoice['file'], $o)) echo $invoice['file'];
	else echo '!'.T_('Error');
}
else $pdf->Output('D',$invoice['file']); // Direct Download to browser
//
//
function Tiso_($f) {
	if(function_exists('T_')) $f = T_($f);
	return mb_convert_encoding($f, 'ISO-8859-1', 'UTF-8');
}
?>
