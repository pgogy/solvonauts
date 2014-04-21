function add_to_list(link, title, desc){

	$.cookie(link, title + "<br />" + desc);
	
}

function get_list(){

	data = $.cookie();
	console.log(data);
	for(x in data){
		console.log(x);
		console.log(data[x]);
	}
	
}