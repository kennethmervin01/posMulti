function PaymentProcess(){
	this.credit = 0;
	this.balance = 0;

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
				will_pay[customer_cart[i].custid] = {remit_amt: customer_cart[i].fintotal,balance:0};
				or_payment = or_payment - customer_cart[i].fintotal;
			} else {
				balanse = customer_cart[i].fintotal - or_payment;
				will_pay[customer_cart[i].custid] = {remit_amt: or_payment,balance: balanse};
				total_balance = total_balance + balanse;
				or_payment = 0;
			}
		}

		this.credit = or_payment;	
		this.balance  = total_balance;
		console.log(will_pay);

	}, 

	generateData:function(){
		var payment = this.payment;
		var single = cart_computations_single;

		var real_or = { // for real or table
			branchid: "PH001",
			real_or_num: payment.or_payment,
			real_or_amount: payment.or_payment,
			real_or_date: payment.or_date, 
			real_or_type: 0, 
			real_or_ref: 0, 
			real_or_remark: 0, 
			date_input: 0,
			balance: this.balance,
			credits: this.credit
		};
	}
}


