// add book in cart 
$("#search-book").autocomplete({
	source: function(request,response){
		$.ajax({
			url:"php/search-item.php",
			type:"post",
			data:{term:request.term, branch:"PH002", method:"search_item"},
			dataType:"json",
			success:function(data){
				console.log(data);
				response(data);
			},error:function(err){
				console.log(err.responseText);
			}	
		})
	}, //source
	minLength:3,
	select:function(event,ui){
		add_cart_book(ui.item.value);
		$(this).val(''); 
		return false;
	}
}).data("ui-autocomplete")._renderItem = function(ul,item){
	$li = $("<li>");
	$li.append(draw_preselect_items(item));
	return $li.appendTo(ul)
}

//add item to cart
$(".clickable-item").click(function(){
	var item = $(this);
	add_cart(item);
});

$(".click-bundle").click(function(){
	var bundle   = $(this).data();
	add_bundle(bundle,add_discount);
});

function add_discount(bundle){
	var cart     = new ItemCart();
	var cartProcess = new CartProcess();
	var discount = {
		type:"1", 
		value: bundle.discpercent,
		keyword:bundle.disc,
		desc:bundle.discdesc
	};
	cartProcess.addDiscount(discount);
	cart.totalCart();
}

//remove item in cart
$(".customer-container").on("click",".removecart", function(){
	var elem = $(this);
	var cart = new ItemCart();
	cart.removeItemRow(elem);
	cart.totalCart(elem);

});

//update item in cart 
$(".customer-container").on("keyup change",".qty-sku",function(){
	var cart = new ItemCart();
	var sku = $(this).data('sku');
	var val = $(this).val();
	cart.updateItemRow(sku,val);
	//console.log(cart_container);
});



//add discount
$(".available-items-container").on("click",".discount-button",function(){
	var  discount = $(this).data();
	var cart   = new ItemCart();
	var cartProcess = new CartProcess();
	cartProcess.addDiscount(discount);
	cart.totalCart();
});


//remove discount
$(".customer-container").on("click",".remove-discount",function(){
	var cart =  new ItemCart();
	var cartProcess = new CartProcess();
	cartProcess.removeDiscount();
	cart.totalCart();
});

// referral  button
$(".customer-container").on("click",".referral-but",function(){
	var active_id = $(this).data("activeid");
	var refqty    = $(this).data("refqty");
	var mybut = $(this); 
	var cart =  new ItemCart();
	var cartProcess = new CartProcess();
	cartProcess.addReferral(refqty,function(request){
		if(request == 0){
			new DialogHelper("Hello Teacher","Please select a MODULE FEE to add a referral discount").createDialog();	
		} else {
			mybut.fadeOut();
		}
	});
	cart.totalCart();
});

// compute all in cart
$("#compute-now").one("click",function(){
	var cp = new CartProcess();
	$(".hide-in-payment, .removecart, .remove-discount").fadeOut();
	$(".make-it-readonly").attr("readonly",true).css("border","none");
	$(".customer-row").removeClass("active-customer").removeClass("customer-draw");
	$(".grayout").css({"opacity":"1","filter":"alpha(opacity = 100)"});
	$(this).fadeOut();
	$(".payment-options").show();
	cp.computeSingle();
	cp.computeAll(function(x){
		var sub_total 	= myMoneyFormat(x.subtotal);
		var disc_total  = myMoneyFormat(x.disctotal);
		var total       = myMoneyFormat(x.supertotal);
		$(".subtotal_total_cart").html(sub_total);
		$(".discount_total_cart").html(disc_total);
		$(".super_total_cart").html(total);
	});
});


$(".pay-option").click(function(){
	var	ptype = $(this).data(); 
	var po = new PaymentOptions(ptype.type,ptype.title);
	po.draw();
});

$(".payment-container").on("click","#submit-payment",function(){
	var payment = [];	
	var paymentProcess = new PaymentProcess();
	$(".payment-form :input").each(function(){
		var input = $(this);
		var names  = input.attr("name");
		var val    = input.val();
		if(val == "" ){
			new DialogHelper("Payment Form","Please Complete the Payment Form").createDialog();
			payment = [];
			return false;
		} else {
			payment[names] = val;
		}
	});  
	var credits = paymentProcess.distribute(payment);
	paymentProcess.generateData();
});


$(".payment-container").on("keyup","#or_payment",function(event){
	var that = $(this);
	decimal_only(that);
});

$(".payment-container").on("keyup","#cheque_no",function(event){
	var that = $(this);
	decimal_only(that);
});

$("#dc").change(function(){
	check_gc_code($(this));
});

/*************************** Function requirements for cart ui *************************************/

function check_gc_code(gc){
	var gc_code =  gc.val();
	$.ajax({
		url:"php/search-gc.php",
		type:"post",
		data:{gc:gc_code},
		dataType:"json",
		success:function(j){
			if(active_id != null && j.result.status == "1"){
				add_cart_ajax(j.items.module_type,"PH002",j.items.module_num,function(xcart){
					if(xcart){ 
						compute_now_magic();
						gc_amount = j.items.module_amount * j.items.module_discount;
						$("#or_payment").val(gc_amount).attr("readonly","readonly");
						disc_percent = parseFloat(j.items.module_discount) * 100;
						mod_string = j.items.module_num + " " + j.items.module_type;
						$(".gc_string").html(j.result.result.gc_code + "<br />" + j.items.scholar_type_description + "<br /> " + mod_string  + "<br />" + disc_percent + "%");
						gc_container = {custno: active_id, gc_code: j.result.result.gc_code,gc_payment:gc_amount};
					}
				});
			} else { 
				warning_text = active_id == null ?  "Please Select A Customer" : "Invalid Voucher Code" ;
				new DialogHelper("Hello Teacher",warning_text).createDialog();
				gc.val("");
			}	
		},
		error:function(err){
			console.log(err.responseText);
		}		
	});
}

function compute_now_magic(){
	var cp = new CartProcess();
	$(".hide-in-payment, .removecart, .remove-discount").fadeOut();
	$(".make-it-readonly").attr("readonly",true).css("border","none");
	$(".customer-row").removeClass("active-customer").removeClass("customer-draw");
	$(".grayout").css({"opacity":"1","filter":"alpha(opacity = 100)"});
	$("#compute-now").fadeOut();
	$(".payment-options").show();
	cp.computeSingle();
	cp.computeAll(function(x){
		var sub_total 	= myMoneyFormat(x.subtotal);
		var disc_total  = myMoneyFormat(x.disctotal);
		var total       = myMoneyFormat(x.supertotal);
		$(".subtotal_total_cart").html(sub_total);
		$(".discount_total_cart").html(disc_total);
		$(".super_total_cart").html(total);
	});
	var po = new PaymentOptions("cash","Cash");
	po.draw();
	$(".pay-option").hide();
}

function decimal_only(that){
	var val = that.val();
    if(isNaN(val)){
         val = val.replace(/[^0-9\.]/g,'');
         if(val.split('.').length>2) 
             val =val.replace(/\.+$/,"");
	}
   	that.val(val); 
}


function draw_preselect_items(item){
	 html = `
		<a style="display:block; text-decoration:none;"><img width="60px" height="70px" src="http://localhost/cshop/page/ajax/`+ item.icon +`" />
		`+item.label+ `</a>`;	
	return html;
}

function add_cart(item){
	var item_num   =  Object.size(item.data());
	for(var i = 0 ; i <= item_num - 1; i++) {
		add_cart_ajax(item.data("sku" + i),"PH002");
	}
}

function add_cart_book(item){
	var customerID = active_id;
	add_cart_ajax(item,"PH002");
}


function add_bundle(bundle,callback){
	$.ajax({
		url:"php/search-item.php",
		type:"post",
		dataType:"json",
		data:{bundle:bundle,method:"get_bundle"},
		success:function(j){
			newItem = new ItemCart(j.itemno,"PH002").drawItemRow(j);
			callback(bundle);
		},error:function(x){
			console.log(x);
		}
	});
}

function add_cart_ajax(sku,branch,qty=1,callback=null){
	var customerID = active_id;
	$.ajax({
		url:"php/search-item.php",
		type:"post",
		data:{term:sku, branch:branch,method:"get_item", qty:qty},	
		dataType:"json",
		success:function(j){
			if(callback){
				newItem = new ItemCart(sku,branch).drawItemRow(j,1);
				callback(j);
			} else {
				newItem = new ItemCart(sku,branch).drawItemRow(j);
			}	
		},error:function(err){
			console.log(err.responseText);
		}
	});	
}

function myMoneyFormat(value){
	var num = value.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
	return num;
}	


/************************************************************************************************/