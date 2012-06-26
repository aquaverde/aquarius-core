<?php

function smarty_modifier_replacelogo($text) 
{	
	global $smarty;

	$replacer = array(
		
		"/^\* Garni Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_1_Sterne_HotelGarni.jpg' alt='Garni Gastrosuisse' />" ,
        "/^\* Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_1_Sterne_Hotel.jpg' alt='Gastrosuisse' />" ,
        "/^\*\* Garni Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_2_Sterne_HotelGarni.jpg' alt='Garni Garni Gastrosuisse' />" ,
        "/^\*\* Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_2_Sterne_Hotel.jpg' alt='Gastrosuisse' />" ,
        "/^\*\*\* Garni Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_3_Sterne_HotelGarni.jpg' alt='Garni Gastrosuisse' />" ,
        "/^\*\*\* Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_3_Sterne_Hotel.jpg' alt='Gastrosuisse' />" ,
        "/^\*\*\*\* Garni Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_4_Sterne_HotelGarni.jpg' alt='Garni Gastrosuisse' />" ,
        "/^\*\*\*\* Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_4_Sterne_Hotel.jpg' alt='Gastrosuisse' />" ,
        "/^\*\*\*\*\* Garni Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_5_Sterne_HotelGarni.jpg' alt='Garni Gastrosuisse' />" ,
        "/^\*\*\*\*\* Gastrosuisse/" => "<img src='/pictures/pictos/gastro/alt_5_Sterne_Hotel.jpg' alt='Gastrosuisse' />" ,


        "/^\* Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_1Stern_o.jpg' alt='Hotelleriesuisse' />" ,
        "/^\*\* Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_2Sterne_o.jpg' alt='Hotelleriesuisse' />" ,
        "/^\*\*\* Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_3Sterne_o.jpg' alt='Hotelleriesuisse' />" ,
        "/^\*\*\*\* Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_4Sterne_o.jpg' alt='Hotelleriesuisse' />" ,
        "/^\*\*\*\*\* Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_5Sterne_o.jpg' alt='Hotelleriesuisse' />" ,
        "/^\*\*\*\*\*sup Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_5SterneSUP_o.jpg' alt='sup Hotelleriesuisse' />" ,
        "/^\*\*\*\*sup Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_4SterneSUP_o.jpg' alt='sup Hotelleriesuisse' />" ,
        "/^\*\*\*sup Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_3SterneSUP_o.jpg' alt='sup Hotelleriesuisse' />" ,
        "/^\*\*sup Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_2SterneSUP_o.jpg' alt='sup Hotelleriesuisse' />" ,
        "/^\*sup Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_1SterneSUP_o.jpg' alt='sup Hotelleriesuisse' />" ,
        "/0 Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Hotel_0Sterne_o.jpg' alt='Hotelleriesuisse' />" ,
        "/International Chain Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Intern_Chain_Hotel_o.jpg' alt='International Chain Hotelleriesuisse' />" ,
        "/Swiss Lodge Hotelleriesuisse/" => "<img src='/pictures/pictos/gastro/alt_Swiss_Lodge_o.jpg' alt='Swiss Lodge Hotelleriesuisse' />" ,
		
	);
	
	foreach($replacer as $toreplace => $with)
	{
		$muster		= $toreplace;
		$ersetzung 	= $with;
		$text 		= preg_replace($muster, $ersetzung, $text);
	}		
 		
	return $text;	
}
?>