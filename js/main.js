var active_id = null; // selected cutomer
var cart_container =[]; // cart items 
var cart_clients   = []; // customers
var cart_computations_single = []; // cart computation like total sum of cart items vat exempt
var cart_computations_all = []; // cart computation like total sum of cart items vat exempt
var discount_container = []; // discount per person
var referral_container = []; // referral discount 
var will_pay = []; // distrubution of payment and balance 
// search autocomplete for customer
$("#customer").autocomplete({
	source:function(request,response){
		$.ajax({
			url:"php/search-customer.php",
			type:"post",
			data:{
				term: request.term,
				customer_nos: $("#customer_id").val()
			},
			dataType:"json",
			success:function(data){
				response(data);
			}, error:function(request,error){
				console.log(request.responseText);
			}
		});
	},
	minLength:3,
	select:function(event,ui){
		var custnos = $("#customer_id").val();
		cart_clients.push(ui.item.value);
		$("#customer_id").val(custnos + ui.item.value + ",");
		$("#customer").val("");
		draw_customer(ui);
		checkIfCustomer();
		return false;
	}
});

//Prepend Customer  in .Customer-container
function draw_customer(data){
	var credit =   data.item.credit; // refferal
	var maxcredit = credit >= 2 ? 2 : credit;
	var refbutton = maxcredit != 0 ? `<button class='btn btn-primary btn-sm referral-but' data-activeid = '`+ data.item.value +`'>ADD `+ maxcredit +` Referral </button>`: '';

	$(".customer-draw").addClass("grayout").removeClass("active-customer");	
	var html = ` 
	<div class='customer-row customer-draw active-customer' data-id='`+ data.item.value +`' id='order-items-` + data.item.value + `'>
    <div class='row'>
      <div class='col-sm-12'>
        <h4>` + data.item.label +`</h4>
        <h5 class='hide-in-payment'>Referral Credits(`+credit+`):`+ refbutton + `</h5>
      </div>
    </div>  

    <div class='row'>
      <div class='col-sm-12'>
        <table class='table table-bordered' id='item-table-`+ data.item.value +`'>
          <thead>
            <tr>
              <th>ItemNo</th>
              <th>Description</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr class="total">
              <td colspan='4' style="text-align:center;">Total</td>
              <td class="total-`+ data.item.value +`"></td>
            </tr>
          </tbody>
        </table>
      </div> 
    </div>
  </div>`;
  $(".customer-container").prepend(html);
  active_id = data.item.value;
}


// Select active customer
$(".customer-container").on("click",".customer-draw",function(){
	var id = $(this).attr("data-id");
	select_customer_ui(id);
	move_item_selection($(this));
	checkIfCustomer();
});

// grayout effects for active user
function select_customer_ui(id){
	$(".customer-draw").addClass("grayout").removeClass("active-customer");	
	$("#order-items-" + id).removeClass("grayout").addClass("active-customer");
	active_id = id;
}

// move item selection to selected customer
function move_item_selection(elem){
	var pos = elem.position(); 
	//console.log(elem);
	$(".available-items-container").animate({"top" :  pos.top +"px" },'slow');
}

//check if  theres a customer in cart  then  show proceed payment button
function checkIfCustomer(){
	if(active_id != "" || active_id != null){
		$("#compute-now").fadeIn();
	}
}



