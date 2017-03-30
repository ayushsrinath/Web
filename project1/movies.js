function initialize () {
}

function sendRequest () {
   var xhr = new XMLHttpRequest();
   var query = encodeURI(document.getElementById("form-input").value);
   xhr.open("GET", "proxy.php?method=/3/search/movie&query=" + query, true);
   xhr.setRequestHeader("Accept","application/json");
   xhr.onreadystatechange = function () {
       if (this.readyState == 4) {
          var json = JSON.parse(this.responseText);
		  var str = JSON.stringify(json,undefined,2); 
		  data_format(str)
       }
   };
   xhr.send();
}

function data_format(str){
		dat = JSON.parse(str)
		var tr = ""
		for (var i=0;i<dat.results.length;i++){
			var r = dat.results[i]
			tr = tr + '<a onclick="movie_data('+r.id+');" href="#">'+r.original_title+ ' - Released:'+r.release_date+'</a>'
			tr = tr + "<br>"
			
		}
		  
        document.getElementById("output").innerHTML = "<pre>" + tr + "</pre>";
	
}



function movie_data(id){
	var xhr = new XMLHttpRequest();
    xhr.open("GET", "proxy.php?method=/3/movie/" +id);
    xhr.setRequestHeader("Accept","application/json");
    xhr.onreadystatechange = function () {
       if (this.readyState == 4) {
          var json = JSON.parse(this.responseText);
		  var str = JSON.stringify(json,undefined,2); 
		  details_format(str)
       }
   };
  xhr.send();
  movie_cast(id)
	
}


function details_format(details){
	dat = JSON.parse(details)
	var tr = ""
	tr = tr + " <h3>"+dat.original_title+" "+"</h3>"
	
	tr = tr + "<h4>Genre: "
	for (var i=0;i < dat.genres.length;i++){
			
			if (i < dat.genres.length){
				tr = tr + ", "
			}
			tr = tr + dat.genres[i].name
	}
	tr = tr+"</h4>"
	tr = tr+"<p \"> Overview: " +dat.overview+"</p>"
	tr = tr + '<img src="http://image.tmdb.org/t/p/w500/'+dat.poster_path+'"/>'	
	tr = tr + "<p id = \"movie_cast\">"+"</p>"	  
    document.getElementById("movie_details").innerHTML = "<pre>" + tr + "</pre>";
}


function movie_cast(id){
	var xhr = new XMLHttpRequest();
    xhr.open("GET", "proxy.php?method=/3/movie/" +id+"/credits");
    xhr.setRequestHeader("Accept","application/json");
    xhr.onreadystatechange = function () {
       if (this.readyState == 4) {
          var json = JSON.parse(this.responseText);
		  var str = JSON.stringify(json,undefined,2); 
		  cast_format(str)
		  
       }
   };
  xhr.send();
	
}


function cast_format(str){
		dat = JSON.parse(str)
		var tr = "CAST: "
		for (var i=0;i<5 && i<dat.cast.length;i++){
			tr = tr + dat.cast[i].name
			if (i <= dat.cast.length){
				tr = tr + ", "
			}
			
			
		}
	document.getElementById("movie_cast").innerHTML = "<pre>" + tr + "</pre>";
}
