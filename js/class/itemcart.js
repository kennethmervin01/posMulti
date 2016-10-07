/***************
Front End Item Cart Class
Created By Kenneth Mervin Enriquez
Method: 
	getDetails - det data in server
	drawItemrow - draw item in cart and run addToCart from  CartProcess Class 
**************/
function ItemCart(sku,branch){
	this.sku = sku;
	this.branch = branch;
	this.desc   = null;
	this.price  = 0;
	this.itemno  = null;
	this.lessonfee = 0;
	this.bookfee  = 0;
	this.otherfee = 0;  
	this.totprice = 0;
	this.qty    = 1;
	this.vatable = 0;
	this.cart   = new CartProcess();
}

ItemCart.prototype = {
	constructor: ItemCart,
	getDetails: function(item){ 
		this.desc  = item.desc;
		this.price = parseFloat(item.price);
		this.itemno = item.itemno;
		this.lessonfee = parseFloat(item.lessonfee);
		this.bookfee  = parseFloat(item.bookfee);
		this.otherfee = parseFloat(item.otherfee); 
		this.totprice = parseFloat(item.totprice); 
		this.vatable  = parseFloat(item.vatable);
		this.qty      = parseFloat(item.qty);
	},
	totalCart: function(){
		var arr = cart_container;

		var discount = 0;
		var disc_type = 1;
		var discounted = 0;
		var disc_tot = 0;
		var discounted_qty = 0;

		var ref_disc = 0;
		var ref_discounted =0;
		var ref_tot = 0;
		
		var subtotal = 0;	
		var tot = 0;


		for (var i in arr) {
			if (arr[i].customerid == active_id){
				subtotal      += arr[i].totprice;	// calculating sum of all items			
				discounted    += arr[i].bookfee + arr[i].lessonfee; // total bookfee  and lessonfee
				arr[i].bookfee >= 1 ? discounted_qty = parseFloat(arr[i].qty) : void 0; // geting qty of module fees
			}
		}

		discounted == 0 ? $("#item-table-" + active_id + " .discountrow").remove() : void 0; // remove  discount row if no modreg
		discounted == 0 ? this.cart.removeDiscount() : void 0; // remove discount in discount_container
		if(discount_container[active_id]){ // set discount if true
			discount  = discount_container[active_id].discount;
			disc_type = discount_container[active_id].type;
			var strbutton = "<button class='btn btn-danger remove-discount'>X</button>";
			$("#item-table-" + active_id + " .discountrow").remove();
			$("#item-table-" + active_id + " tbody tr:last").before("<tr class='discountrow'><td colspan='4' style='text-align:center'>"+ strbutton + discount_container[active_id].desc +"</td><td class='discamt'></td></tr>");
			disc_tot = 	discounted * discount;
		} else {
			$("#item-table-" + active_id + " .discountrow").remove();
		}		
		tot      =  subtotal - disc_tot;
		$("#item-table-" + active_id + " .discamt").html(disc_tot);
		if(referral_container[active_id]){
			$("#item-table-" + active_id + " .discountrefrow").remove();
			$("#item-table-" + active_id + " tbody tr:last").before("<tr class='discountrefrow'><td colspan='4' style='text-align:center'>Referral Discount</td><td class='rdiscamt'></td></tr>");
			ref_disc = referral_container[active_id].discount;
			ref_discounted = (discounted - disc_tot) / discounted_qty;
			console.log(discounted_qty);
			ref_tot		   = ref_discounted * ref_disc;	
		}
		tot    =  tot - ref_tot; 	
		$("#item-table-" + active_id + " .rdiscamt").html(ref_tot);
		$(".total-" + active_id).html(tot);
	},
	drawItemRow: function(item){
		var doNow = CartHelper.checkItemExist(this.sku,item.qty,function(myqty,mysku){
			new ItemCart().updateItemRow(mysku,myqty);
		}); // already exist
		if(doNow){
			this.getDetails(item);	
			var x = `<button data-sku='`+ this.sku +`' class='removecart btn btn-danger'>X</button> ` + this.sku;
			var item_row = `
				<tr class="cart-item" data-sku="`+ this.sku +`">
					<td>`+ x +`</td>
					<td>`+ this.desc  +`</td>
					<td>`+ this.price +`</td>
					<td><input type="number" class="qty-sku make-it-readonly" data-sku="`+ this.sku +`" style="width:50px;" value="`+ this.qty +`" /></td>
					<td class="sku-total-` + this.sku + `">`+ this.price * this.qty +`</td>
				</tr>
				`;
			$(".row-removable").fadeOut();
			$("#item-table-" + active_id + " tbody" ).prepend(item_row);
			this.cart.addToCart(this.sku,this.price,this.qty,this.bookfee,this.lessonfee,this.otherfee,this.totprice,this.vatable);
			this.totalCart();
		}
	},
	removeItemRow: function(elem){
		var sku = elem.data("sku");
		var cart = this.cart;
		$("#item-table-"+ active_id +" > tbody >tr" ).each(function(){
			var csku =  $(this).data("sku"); 
			if(csku == sku){
				cart.removeCart(sku);
				$(this).remove();	
				console.log(cart_container);
			}
		});
	},
	updateItemRow:function(sku,qty){
		var item_tot = this.cart.updateCart(sku,qty);
		$("#item-table-"+ active_id +" > tbody >tr > td.sku-total-" + sku).html(item_tot);
		this.totalCart();
	}


	

};

