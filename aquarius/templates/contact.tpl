{extends main.tpl}
{block name='content'}

{literal}
<script type="text/javascript">
	$(document).ready(function() {
		var m = $("#map")[0];
		var myLatlng = new google.maps.LatLng{/literal}({$latLng_x},{$latLng_y}){literal};
		var myOptions = {
			zoom: 11,
			center: new google.maps.LatLng{/literal}({$latLng_x+0.020},{$latLng_y}){literal},
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		var map = new google.maps.Map(m, myOptions);
		var contentString = {/literal}'<div id="info">'+
			'<div id="bodyContent">'+
			'<p><strong>{$company}</strong><br />' +
			'{$street}<br />'+
			'{$city}</p>'+
			'<p><a href="{$googlemaps_url}" target="_blank" style="font-size: 14px;" >{wording Grössere Kartenansicht anzeigen}</a>' +
			'</div>'+
			'</div>'{literal};
		
		var infowindow = new google.maps.InfoWindow({
			content: contentString
		});
		var marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			title: "{/literal}{$company}, {$street}, {$city}{literal}"
		});
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
		infowindow.open(map,marker);
	});
</script>
{/literal}

<div id="leftCol">
    <div class="address">
        <h1>{$title2|default:$title}{edit}</h1>
        <p><strong>{$company}</strong><br />
        {$street}<br />
        {$city}</p>
        <p>{$phone}<br />
        {$fax}</p>
        <p>{$email|email}</p>
    </div>
    <div id="map"></div>
</div>

{/block}