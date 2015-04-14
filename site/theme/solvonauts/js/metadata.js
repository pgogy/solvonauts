$(document).ready(

	function(){
	
		$(".attrib_full")
			.each(
			
				function(index,value){
				
					$(this).click(
						function(){
						
							$.ajax({
							  type: "GET",
							  url: "?action=json_metadata",
							  data: { id: $(this).attr("link_id") }
							}).done( function(response){
							
								data = eval(response);
								
								link = data['link'];
								output = "<span itemprop='name' property='dc:title' >" + data['TITLE'] + "</span>";

								if(data['CREATOR']!=undefined){
									output += " by <span itemprop='author' property='dc:creator'>";
									if( Object.prototype.toString.call(data['CREATOR']) === '[object Array]' ) {
										output += data['CREATOR'].join(",");
									}else{
										output += data['CREATOR'];
									}
									output += "</span>";
								}	

								if(data['LICENSE']!=undefined){
									output += " is licensed as <span itemprop='author' property='dc:creator'><a rel='license' itemprop='useRightsUrl' property='dc:rights'";
									if( Object.prototype.toString.call(data['LICENSE']) === '[object Array]' ) {
										href = "";
										html = "";
										for(x in data['LICENSE']){
											if(x.indexOf("http")==-1){
												html = x;
											}else{
												href = x;
											}
										}
										if(href!=""){
											output += " href='" + href + "'>" + html + "</a></span>";
										}else{
											output += ">" + html + "</a></span>";
										}
									}else{
										output += ">" + data['LICENSE'] + "</a>";
									}
									output += "</span>";
								}	
								
								window.prompt("Copy and Paste HTML\n\nCopy to clipboard: Ctrl+C, Enter", "<div itemscope about='" + link + "'>" + output + "</div>");
								
							});
						
						}
					
					);
				
				}
			
			);
	
	}

);

$(document).ready(

	function(){
	
		$(".attrib_basic")
			.each(
			
				function(index,value){
				
					$(this).click(
						function(){
						
							$.ajax({
							  type: "GET",
							  url: "?action=json_metadata",
							  data: { id: $(this).attr("link_id") }
							}).done( function(response){
								data = eval(response);
								output = data['TITLE'];
								
								if(data['CREATOR']!=undefined){
									if( Object.prototype.toString.call(data['CREATOR']) === '[object Array]' ) {
										output += " by " + data['CREATOR'].join(",");
									}else{
										output += " by " + data['CREATOR'];
									}
								}	

								if(data['LICENSE']!=undefined){
									if( Object.prototype.toString.call(data['LICENSE']) === '[object Array]' ) {
										output += " is licensed as " + data['LICENSE'].join(",");
									}else{
										output += " is licensed as " + data['LICENSE'];
									}
								}	
								
								window.prompt("Attribution\n\nCopy to clipboard: Ctrl+C, Enter", output);
								
							});
						
						}
					
					);
				
				}
			
			);
	
	}

);

$(document).ready(

	function(){
	
		$(".attrib_url")
			.each(
			
				function(index,value){
				
					$(this).click(
						function(){
						
							$.ajax({
							  type: "GET",
							  url: "?action=json_metadata",
							  data: { id: $(this).attr("link_id") }
							}).done( function(response){
							
								data = eval(response);
								
								link = data['link'];
								
								window.prompt("URL \n\nCopy to clipboard: Ctrl+C, Enter", link);
								
							});
						
						}
					
					);
				
				}
			
			);
	
	}

);