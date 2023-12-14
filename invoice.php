<?php
session_start(); 
if(!isset($_POST['unox']) || $_POST['unox']!=$_SESSION['unox']) {sleep(2);exit;} // appel depuis uno.php
?>
<?php
include('../../config.php');
include('lang/lang.php');
if(!file_exists('../../data/invoice.json')) file_put_contents('../../data/invoice.json', '{}');
if(!file_exists('../../../files/invoice')) mkdir('../../../files/invoice');
// ********************* actions *************************************************************************
if(isset($_POST['action'])) {
	switch ($_POST['action']) {
		// ********************************************************************************************
		case 'plugin': ?>
		<div class="blocForm">
			<h2>Invoice</h2>
			<p><?php echo T_("Allows you to create an invoice with one or more predefined formats. PDF output."); ?></p>
			<h3><?php echo T_("Setting"); ?></h3>
			<table class="hForm">
				<tr>
					<td><label><?php echo T_("Invoice Header Image");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceLogo" id="invoiceLogo" value="" />
						<div class="bouton finder" style="margin-left:30px;" id="bFMarkdown" onClick="f_finder_select('invoiceLogo')" title="<?php echo T_("File manager");?>"><img src="<?php echo $_POST['udep']; ?>includes/img/finder.png" /></div>
					</td>
					<td><em><?php echo T_("Recommended size between 100px and 200px.");?></em></td>
				</tr>
				<tr>
					<td style="vertical-align:middle"><label><?php echo T_("Business address");?></label></td>
					<td>
						<textarea class="input" style="width:350px" name="invoiceAddr" id="invoiceAddr" rows="7"></textarea>
					</td>
					<td><em><?php echo T_("Company information, right of the logo.");?></em></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Invoicing details");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceComp" id="invoiceComp" value="" />
					</td>
					<td><em><?php echo T_("Invoicing supplement, displayed in italics below the table.");?></em></td>
				</tr>
				<tr>
					<td style="vertical-align:middle"><label><?php echo T_("Invoice Footer");?></label></td>
					<td>
						<textarea class="input" style="width:350px" name="invoiceFoot" id="invoiceFoot" rows="7"></textarea>
					</td>
					<td></td>
				</tr>
			</table>
			<div class="bouton fr" onClick="f_save_invoice();" title="<?php echo T_("Save settings");?>"><?php echo T_("Save");?></div>
			<div class="clear"></div>
			<hr />
			<h3><?php echo T_("Test invoice"); ?></h3>
			<table class="hForm">
				<tr>
					<td><label><?php echo T_("First name");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceFname" id="invoiceFname" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Last name");?>*</label></td>
					<td>
						<input type="text" class="input" name="invoiceLname" id="invoiceLname" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Email");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceEmail" id="invoiceEmail" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Address");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceAdres" id="invoiceAdres" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Product");?>*</label></td>
					<td>
						<input type="text" class="input" name="invoiceProdu" id="invoiceProdu" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Product REF");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceRef" id="invoiceRef" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Date");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceDate" id="invoiceDate" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Price");?>*</label></td>
					<td>
						<input type="text" class="input" name="invoicePrice" id="invoicePrice" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Shipping price");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceShip" id="invoiceShip" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Tax % (0 to 1)");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceTax" id="invoiceTax" value="" />
					</td>
					<td><?php echo T_("Example for 20% : 0.2");?></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Currency");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceCurr" id="invoiceCurr" value="" />
					</td>
					<td><?php echo T_("Default is EURO");?></td>
				</tr>
				<tr>
					<td><label><?php echo T_("Payment method");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceMetho" id="invoiceMetho" value="" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td><label><?php echo T_("File name");?></label></td>
					<td>
						<input type="text" class="input" name="invoiceFile" id="invoiceFile" value="" />
					</td>
					<td></td>
				</tr>
			</table>
			<div class="bouton fr" onClick="f_test_invoice();" title="<?php echo T_("Test invoice");?>"><?php echo T_("Create PDF");?></div>
			<div class="clear"></div>
		</div>
		<?php break;
		// ********************************************************************************************
		case 'save':
		$a = array();
		if(isset($_POST['logo'])) {
			if(substr($_POST['logo'],0,5)=='/file') $a['logo'] = stripslashes(realpath(__DIR__ . '/../../..'.$_POST['logo']));
			else $a['logo'] = $_POST['logo'];
		}
		if(isset($_POST['addr'])) $a['addr'] = strip_tags($_POST['addr']);
		if(isset($_POST['comp'])) $a['comp'] = strip_tags($_POST['comp']);
		if(isset($_POST['foot'])) $a['foot'] = strip_tags($_POST['foot']);
		$out = json_encode($a);
		if(file_put_contents('../../data/invoice.json', $out)) echo T_('Backup performed');
		else echo '!'.T_('Impossible backup');
		break;
		// ********************************************************************************************
		case 'test':
		$invoice = array();
		foreach($_POST as $k=>$v) if($k!='action' && $k!='unox') $invoice[$k] = strip_tags(preg_replace("/\s+/", " ",$v));
		if(!empty($invoice['lname']) && !empty($invoice['produ']) && !empty($invoice['price'])) {
			$invoice['output'] = 'S';
			include('invoiceCreatePdf.php');
		}
		else echo '!'.T_('Error');
		break;
		// ********************************************************************************************
	}
	clearstatcache();
	exit;
}
?>
