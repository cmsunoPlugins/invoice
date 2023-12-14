//
// CMSUno
// Plugin Invoice
//
function f_save_invoice(){
	let h=new FormData();
	h.set('action','save');
	h.set('unox',Unox);
	h.set('logo',document.getElementById('invoiceLogo').value);
	h.set('addr',document.getElementById('invoiceAddr').value);
	h.set('comp',document.getElementById('invoiceComp').value);
	h.set('foot',document.getElementById('invoiceFoot').value);
	fetch('uno/plugins/invoice/invoice.php',{method:'post',body:h})
	.then(r=>r.text())
	.then(r=>f_alert(r));
}
function f_test_invoice(){
	let h=new FormData();
	h.set('action','test');
	h.set('unox',Unox);
	h.set('fname',document.getElementById('invoiceFname').value);
	h.set('lname',document.getElementById('invoiceLname').value);
	h.set('email',document.getElementById('invoiceEmail').value);
	h.set('adres',document.getElementById('invoiceAdres').value);
	h.set('produ',document.getElementById('invoiceProdu').value);
	h.set('ref',document.getElementById('invoiceRef').value);
	h.set('date',document.getElementById('invoiceDate').value);
	h.set('price',document.getElementById('invoicePrice').value);
	h.set('ship',document.getElementById('invoiceShip').value);
	h.set('tax',document.getElementById('invoiceTax').value);
	h.set('curr',document.getElementById('invoiceCurr').value);
	h.set('metho',document.getElementById('invoiceMetho').value);
	h.set('file',document.getElementById('invoiceFile').value);
	fetch('uno/plugins/invoice/invoice.php',{method:'post',body:h})
	.then(r=>r.text())
	.then(function(r){
		if(r&&r.substring(0,1)!='!')window.open('files/invoice/'+r,'_blank').focus();
		else f_alert(r);
	});
}
//
function f_load_invoice(){
	fetch("uno/data/invoice.json?r="+Math.random())
	.then(r=>r.json())
	.then(function(data){
		if(data.logo)document.getElementById('invoiceLogo').value=data.logo;
		if(data.addr)document.getElementById('invoiceAddr').value=data.addr;
		if(data.comp)document.getElementById('invoiceComp').value=data.comp;
		if(data.foot)document.getElementById('invoiceFoot').value=data.foot;
	});
}
//
f_load_invoice();
