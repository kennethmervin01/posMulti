<script>
x = 1
promise = new Promise(function(hello,hi){
	if(x == 1){
		hello("hello");
	} else {
		hi("hi");
	}
}) ;

console.log(promise);
</script>