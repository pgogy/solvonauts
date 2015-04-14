$(document).ready(

	function(){
	
		$(".oerlink")
			.each(
			
				function(index,value){
				
					$(this).click(
						function(){
						
							$.ajax({
							  type: "POST",
							  url: "?action=activity",
							  data: { id: $(this).attr("link_id") }
							});
						
						}
					
					);
				
				}
			
			);
	
	}

);