
var map;
var markers = [];
function initialize () {
	var uluru = {lat: 32.75, lng: -97.13};
  
	map = new google.maps.Map(document.getElementById('map'), {
    zoom: 16,
    center: uluru
  });
  var marker = new google.maps.Marker({
    position: uluru,
    map: map
  });
}


//Method to send AJAX request
function sendRequest () {
	
   for (var j = 0; j < markers.length; j++) {
          markers[j].setMap(null);
        }	
   markers = [];
   var xhr = new XMLHttpRequest();
   var query = encodeURI(document.getElementById("search").value);
   var north_east_lat = map.getBounds().getNorthEast().lat();
   var north_east_lng = map.getBounds().getNorthEast().lng();
   var south_west_lat = map.getBounds().getSouthWest().lat();
   var south_west_lng = map.getBounds().getSouthWest().lng();
   xhr.open("GET","proxy.php?term="+query+"&bounds="+south_west_lat+","+south_west_lng+"|"+north_east_lat+","+north_east_lng+"&limit=5");
   xhr.setRequestHeader("Accept","application/json");
   xhr.onreadystatechange = function () {
       if (this.readyState == 4) {
          var json = JSON.parse(this.responseText);
          var str = JSON.stringify(json,undefined,2);
          //document.getElementById("output").innerHTML = "<pre>" + str + "</pre>";
		  display_data(str);
       }
   };
   xhr.send(null);
}

//Method to display search results
function display_data (data_sent) {
		dat = JSON.parse(data_sent);
		var tr = "";
		if (dat.businesses.length == 0){
			document.getElementById("output").innerHTML = "<pre> No Results Found </pre>";
		}
		
		else {
			
		
		for (var i =0 ; i < dat.businesses.length && i < 10;i++){
			var lab = i+1;
			
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(parseFloat(dat.businesses[i].location.coordinate.latitude),parseFloat(dat.businesses[i].location.coordinate.longitude)),
				map: map,
				label: lab.toString()
				
			});
			marker.setMap(map);
			tr = tr + "<li>";
			tr = tr + "<img src='"+dat.businesses[i].image_url+"'/>";
			tr = tr + "<a href="+dat.businesses[i].url+">"+dat.businesses[i].name+"</a>";
			tr = tr + "<p> Rating <img src='"+dat.businesses[i].rating_img_url+"'/></p>";
			tr = tr + "<p>Snippet :"+dat.businesses[i].snippet_text+"</p>";
			tr = tr + "</li>";
			markers.push(marker);
		}
		
		document.getElementById("output").innerHTML = "<pre><ol>" + tr + "</ol></pre>";
		}
}












