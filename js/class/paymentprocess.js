function PaymentProcess(){
	this.credit = 0;
	this.balance = 0;
	this.willpay = null;


}

PaymentProcess.prototype = {
	constructor:PaymentProcess,
	distribute: function(payment){
		this.payment = payment;
		var customer_cart = cart_computations_single;
		var or_payment =  payment.or_payment;	
		var total_balance = 0; 
		for(var i in customer_cart ){
			if(or_payment >= customer_cart[i].fintotal ){
				will_pay_data = {custno: customer_cart[i].custid, remit_amt: customer_cart[i].fintotal,balance:0};
				will_pay.push(will_pay_data);
				or_payment = or_payment - customer_cart[i].fintotal;
			} else {
				balanse = customer_cart[i].fintotal - or_payment;
				will_pay_data = {custno: customer_cart[i].custid,remit_amt: or_payment,balance: balanse};
				will_pay.push(will_pay_data);
				total_balance = total_balance + balanse;
				or_payment = 0;
			}
		}

		this.willpay = will_pay;
		this.credit = or_payment;	
		this.balance  = total_balance; 
	}, 

	generateData:function(){
		var real_or = this.genRealOR();
		var dist_or = this.genDistOR();

		this.sendData(real_or,dist_or);		
	},
	genRealOR:function(){
		var payment = this.payment;
		var real_or;
		var scode = gc_container.gc_code ? gc_container.gc_code :  null ;
		var real_or_status = gc_container.gc_code ?  "AR":  "OR" ;
		console.log(fin_cart);
		if(payment.ptype == "cash" || payment.ptype == "directdeposit"){
			real_or = { // for real or table
				branchid      : "PH001",
				real_or_num   : payment.or_number,
				real_or_amount_paid: payment.or_payment,
				real_or_amount_due: fin_cart.supertotal,// total gc payment + or payment
				real_or_date  : payment.or_date, 
				real_or_type  : payment.ptype, 
				balance       : this.balance,
				credits       : this.credit,
				scholar_code  : scode,
				real_or_status: real_or_status 
			};
		} else {
			 real_or = { // for real or table
				branchid      : "PH001",
				real_or_num   : payment.or_number,
				real_or_amount_paid   : payment.or_payment,
				real_or_amount_due: fin_cart.supertotal,
				real_or_date  : payment.or_date, 
				real_or_type  : payment.ptype, 
				balance       : this.balance,
				credits       : this.credit,
				cheque_num    : payment.cheque_no,
				cheque_date   : payment.cheque_date,
				bank_name     : payment.bank_name,
				bank_branch   : payment.bank_branch,
				scholar_code  : scode,
				real_or_status: real_or_status  
			};
		}
		console.log(real_or);
		return real_or;
	}, 

	genDistOR:function(){
	   var clients  =   cart_clients; 
	   var single_or = [];
	   for( i in clients){
	   		recollect = this.distHelperSingle(clients[i]);
	   		single_or.push(recollect);
	   }
	   console.log(single_or);
	   return single_or;
	},

	distHelperSingle:function(clientID){
		var cart   =  cart_container;
		var single =  cart_computations_single;
		var single_or = [];
		var cart_single = [];
		for (i in cart){
			if(cart[i].customerid == clientID){
				cart_single.push(cart[i]);
			}
		}

		for(i in single){
			if(single[i].custid == clientID){
				single_or = {
					or_head : single[i],
					or_details: cart_single
				}
				break;
			}
		}
		return single_or;
	},

	sendData:function(real_or,dist_or){
		var willpay = this.willpay;
		$.ajax({
			url:"php/sendData.php",
			type:"post",
			data: { real_or:real_or, dist_or:dist_or,willpay:willpay},
			dataType:"json",
			success:function(j){
				console.log(j);
				alert("Succes");
			},
			error:function(xhr){
				console.log(xhr.responseText);
			}
		});
	}


}


