<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sci-fi Scraper</title>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>          
<script type="text/javascript">
    $(document).ready( function() { 
	    $.ajax({
	        type: "GET",
	        url: "scifi_scraper.php",
	        dataType: "json",
	        async: false,
	        success: function(JSONObject) {
	        	var content = "";
	        	for (var key in JSONObject) {
	        		if (JSONObject.hasOwnProperty(key)) {
	        			content += "<div class='movie'>";
            			content += "<div class='image'><img src='" + JSONObject[key]["image_src"] + "'></div>";
	        			content += "<div class='info'>";
            			content += "<div class='title'><h3>" + JSONObject[key]["title"] + "</h3></div>";
	        			content += "<div class='director_stars'>";
            			content += "<div class='director'><strong>Director: </strong>" + JSONObject[key]["director"] + "</div>";
            			content += "<div class='stars'><strong>Stars: </strong>" + JSONObject[key]["stars"] + "</div>";
            			content += "<div class='stars'><strong>Release Date: </strong>" + JSONObject[key]["release_date"] + "</div>";
            			content += "<div class='stars'><strong>IMDB Rating: </strong>" + JSONObject[key]["rating"] + "</div>";
          				content += "</div>";
          				content += "</div>";
            			content += "<div class='storyline'>" + JSONObject[key]["storyline"] + "</div>";
          				content += "</div>";
          			}
          		}

	            $("#php_output").html(content);
                $("#spinner").hide();
            	$("#php_output").show();
     		},
     		error: function(xhr, status, error){
         		var errorMessage = xhr.status + ': ' + xhr.statusText;
         		alert('Error - ' + errorMessage);
	        }
        });
    });
</script> 

<style>

	body {
		font-family:  Arial;
	}

	h3 {
		margin: 0px 0px 8px 0px;
	}

	#main {
		margin: 20px auto;
		max-width: 1000px;
		text-align: center;
	}

	#spinner {
		padding-top: 15%;
		text-align: center;
	}

	#php_output {
		display: none;
	}

	.movie {
		border: 1px solid #ccc;
    	display: inline-block;
		margin: auto;
		margin-bottom: 20px;
		text-align: left;
		width: 600px;
	}

	.image {
		float: left;
		height: 120px;
		padding: 15px;
		width: 15%;
	}

	.info {
		height: 120px;
		padding: 15px;
	}

	.director_stars {
		font-size: 14px;
		font-style: italic;
	}

	.storyline {
		border-top: 1px solid #ccc;
		padding: 15px;
		text-align: justify;
	}


</style>

</head>

<body>

	<div id="spinner">
		<image src="images/spinner.gif" width="250" height="250">
	</div>

	<div id="main">
		<div id="php_output"></div>
	</div>

</body>

</html>