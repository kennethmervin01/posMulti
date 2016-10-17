/**********************
Cart Process
Created by Kenneth Mervin Enriquez
9/20/2016

Method:
	addtoCart  - initialize cart items to global variable `cart_contatiner` = this.cart
	updateCart - update quantity of  an item on global variable  `cart_contatiner` = this.cart

**********************/
function CartProcess(){
	this.customerid = active_id;
	this.cart =  cart_container;
}


CartProcess.prototype ={
	constructor:CartProcess,
	addToCart : function(sku,price,qty,bookfee,lessonfee,otherfee,totprice,vatable){
		ci =  {
			sku:sku,
			price:price,
			qty:qty,
			customerid:active_id, 
			bookfee:bookfee * qty,
			lessonfee:lessonfee * qty,
			otherfee:otherfee * qty,
			totprice:totprice * qty,
			vatable:vatable * qty,
			orig:{
				lessonfee:lessonfee,
				bookfee:bookfee,
				otherfee:otherfee,
				vatable : vatable
			}
		}; 
		this.cart.push(ci);
	},
	updateCart : function(sku,qty){
		arr = this.cart;
		for (var i in arr) {
			if (arr[i].sku == sku && arr[i].customerid == active_id){
				arr[i].qty = qty; 
				arr[i].totprice = arr[i].price *  qty; 
				arr[i].lessonfee = arr[i].orig.lessonfee * qty;
				arr[i].bookfee = arr[i].orig.bookfee * qty;
				arr[i].otherfee = arr[i].orig.otherfee * qty;
				arr[i].vatable  = arr[i].orig.vatable * qty;
				return arr[i].totprice;
				break; //Stop this loop, we found it!
			}
		}
	},
	removeCart : function(sku){
		arr = this.cart;
		for (var i in arr) {
			if (arr[i].sku == sku && arr[i].customerid == active_id){
				arr.splice(i,1);
				break; //Stop this loop, we found it!
			}
		}	
	},
	computeSingle :function(){ // generate cart computations
		var custid = cart_clients;
		var cart   = cart_container;	
		for (var i in custid){
			custno = custid[i];
			var bookfee_sum = 0;
			var otherfee_sum = 0;
			var lessonfee_sum = 0;
			var price_sum = 0;
			var vatable_sum = 0;
			var total_discount = 0;
			var disc_code = "";
			var disc_remark = "";
			var ref_tot  = 0;
			var ref_qty = 0;
			for(var i in cart){
				if(cart[i].customerid == custno){
					bookfee_sum =  bookfee_sum + cart[i].bookfee;
					otherfee_sum = otherfee_sum + cart[i].otherfee;
					lessonfee_sum = lessonfee_sum + cart[i].lessonfee;
					price_sum = price_sum + cart[i].totprice;
					vatable_sum =  vatable_sum + cart[i].vatable;
					if(discount_container[custno]){
						var fordiscount = lessonfee_sum + bookfee_sum;
						var discount_percent = discount_container[custno].discount;
						total_discount  = fordiscount * discount_percent;
						disc_code =  discount_container[custno].keyword;
						disc_remark =  discount_container[custno].desc;
					}

					if(referral_compute[custno]){
						ref_tot= referral_compute[custno].ref_tot;
						ref_qty = referral_compute[custno].ref_qty;
					}
				}
			}
			var pushItem = {
				custid: custno,
				bookfee: bookfee_sum,
				lessonfee: lessonfee_sum - (total_discount + ref_tot),
				otherfee: otherfee_sum,
				subtotal: price_sum,
				vatable: vatable_sum,
				disckey: disc_code,
				discremark:disc_remark,
				discount: total_discount,
				refdiscount:ref_tot,
				refqty: ref_qty,
				fintotal: price_sum - (total_discount + ref_tot)
			}
			cart_computations_single.push(pushItem);
		}// customer
	},
	computeAll:function(callback){
		var single_cart = cart_computations_single;
		var total       =  0;
		var bookfee     =  0;
		var lessonfee   =  0;
		var otherfee    =  0; 
		var vatable     =  0;
		var disctotal   =  0;
		for(var i in single_cart){
			bookfee   = bookfee   + single_cart[i].bookfee;
			lessonfee = lessonfee + single_cart[i].lessonfee;
			otherfee  = otherfee  + single_cart[i].otherfee;
			total  = total  + single_cart[i].subtotal;
			vatable  = vatable  + single_cart[i].vatable;
			disctotal = disctotal + single_cart[i].discount + single_cart[i].refdiscount ;
		}

		var cart_all = {
			bookfee:bookfee,  
			otherfee:otherfee, 
			lessonfee:lessonfee,  
			total:total, 
			vatable: vatable,
			disctotal:disctotal,
		}  
		cart_computations_all.push(cart_all);


		var total_cart = {
			subtotal: cart_computations_all[0].total,
			disctotal:disctotal,
			supertotal: cart_computations_all[0].total - disctotal 
		};

		callback(total_cart);
		console.log(cart_computations_single);
		console.log(cart_computations_all);
		console.log(discount_container);
	},

	addDiscount:function(discount){
		var trudisc = 0;
		for(i in cart_container){
			if(cart_container[i].customerid == active_id){
				if(cart_container[i].bookfee >= 1 ){
					discount_container[active_id] = {keyword:discount.keyword,discount: parseFloat(discount.value),type:discount.type,desc:discount.desc};	
					trudisc = 1;
					break;	
				} 
			}
		}
		trudisc == 0 ? new DialogHelper("Hello Teacher","Please select a MODULE FEE Items in fees tab to add this discount").createDialog(): void 0;

	},
	removeDiscount:function(){
		var discount = discount_container;
		discount.splice(active_id,1);
		console.log(discount);
	},
	addReferral:function(qty,callback){
		var trudisc = 0;
		for(i in cart_container){
			if(cart_container[i].customerid == active_id){
				if(cart_container[i].bookfee >= 1 ){
					referral_container[active_id] = {discount:.08, rfqty:qty};	
					trudisc = 1;
					break;	
				} 
			}
		}
		console.log(referral_container);
		callback(trudisc);
		//trudisc == 0 ? new DialogHelper("Hello Teacher","Please select a MODULE FEE to add a referral discount").createDialog(): void 0;
	}

} 