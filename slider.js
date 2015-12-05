$(".slider-direction-buttons > a").bind("click", function(e)	{
	var button = $( this ).text();
	var number = 0;
    e.preventDefault();
    
    if(button=="START"){
    	number = 1;
    }
    else if(button=="AKTIER"){
    	number = 2;
    }
    else if(button=="MÖTEN"){
    	number = 3;
    }
    else if(button=="STADGAR"){
    	number = 4;
    }
    else if(button=="MEDLEMMAR"){
    	number = 5;
    }
    else if(button=="NYTT MÖTE"){
        number = 6;
    }   
    
    $(".slider").diyslider("move", number);
});

$('#meetingFormButton').bind('click', function() {
    $(".slider").diyslider("move", 6);
    $('.newMeeting').toggle();
});

var currentHeight = $('#main').height();
var currentWidth = $('#main').width()*0.98;


// initialize the slider
$(".slider").diyslider({
	width: 1.03*currentWidth,
	height: 0.525*currentHeight,
});