/****************Dialog Helper ****************************/
function DialogHelper(title,description){
	this.subject = title;
	this.desc = description;
};

DialogHelper.prototype = {
	constructor:DialogHelper,
	createDialog: function(){
		var title = this.subject;
		var desc  = this.desc; 
		$(".dialog").prop('title',title);
		$(".dialog p").html(desc);
		$(".dialog" ).dialog({ position: { my: 'top', at: 'top+150' },}
		);
	}	
};

/***********************Cart Helper***************************************/
var CartHelper = {
	checkItemExist: function(sku,qty,callback){
		var status = 1;
		$("#item-table-"+ active_id +" > tbody >tr" ).each(function(){
			var csku = $(this).data("sku");
			if(csku == sku){ 
				var go = $(this).find(".qty-sku").val();
				if(go == qty){
					status = 0; new DialogHelper("Hello Teacher","Item Already Exist :)").createDialog();	
				} else {
					$(this).find(".qty-sku").val(qty);
					callback(qty,sku);
					status = 0;
				}
			}
		}) ;
		return status;
	},
}

/*************************Discount********************************/
function Discount(){}
Discount.prototype  = {
	constructor: Discount,
	init: function(filepath,callback){
		//console.log(this);
		var xobj = new XMLHttpRequest();
		xobj.overrideMimeType("application/json");
		xobj.open("GET",filepath,true);
		xobj.onreadystatechange = function (){
			if(xobj.readyState == 4 && xobj.status == "200"){
				callback(xobj.responseText);
			}
		};
		xobj.send(null);  
	}, 
	run: function(response){
		var disc = JSON.parse(response);
		var fullstr = ""; 
		for(i in disc){
			fullstr += Discount.prototype.draw_discount(disc[i].keyword,disc[i].desc,disc[i].type,disc[i].discount);
		}
		$("#discount-sale").html(fullstr);
	},
	draw_discount: function(keyword,desc,type,discount){
		var string = `<button class="btn btn-warning btn-lg discount-button" data-type="`+ type +`" data-value="`+ discount +`" data-keyword="`+ keyword +`" data-desc="`+ desc +`">` + desc + `</button>`;
		return string;
	}	 
};

/*******************Draw Payment Options ***************************/
function PaymentOptions(type,title){
	this.ptype = type;
	this.string = function(addstring = ""){
			var string = `<tr>
	             <td colspan="2" style="text-align:center"><h4>`+ title +`</h4><input type="hidden" name="ptype" value="`+ type +`"></td>
	           </tr>
	           <tr>
	              <td>OR #</td>
	              <td><input type="text" id="or_number" name="or_number"></td>
	           </tr>
	           <tr>
	              <td>OR Date</td>
	              <td><input type="date" id="or_date" name="or_date"></td>
	           </tr>
	           `+ addstring +`
	           <tr>
	              <td>Amount Paid</td>
	              <td><input type="text" id="or_payment" name="or_payment"></td>
	           </tr>
	           <tr>
	              <td colspan="2" style="text-align:center;">
	              <p class="gc_string"></p>
	              <button id="submit-payment" name="finalsubmit" value="go" class="btn btn-success btn-lg">Submit</button>
	              </td>
	           </tr>`; 
	        return string;   	
	}                             
}

PaymentOptions.prototype = {
	constructor: PaymentOptions,
	draw: function(){
		var ptype = this.ptype;
		var html  = this.string();
		var addstring = "";
		if(ptype == "cheque" || ptype =="pdc"){
			addstring += `<tr><td>Bank Name</td><td><textarea name="bank_name"></textarea></td></tr>`;
			addstring += `<tr><td>Bank Branch Name</td><td><textarea name="bank_branch"></textarea></td></tr>`;
			addstring += `<tr><td>Cheque Date</td><td><input type="date" name="cheque_date"></td></tr>`;
			addstring += `<tr><td>Cheque #</td><td><input type="text" name="cheque_no" id="cheque_no"></td></tr>`;
			html = this.string(addstring);
		}
        $(".payment-container").html("<table class='table table-bordered payment-container'>" + html + "</table>");
	}
}


/*******Tools ********/
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
var discount =  new Discount();
discount.init("json/PH001.json",discount.run);
/*********************/