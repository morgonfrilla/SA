
$(document).ready(function(){

	var items = [];
	var stocks = {};
	var nameDict = {};
	var symbolString ='';

	$('#stockTable').tablesorter();	
	getStocksInfo();
	//insertStock(data[i].name, data[i].symbol, data[i].y_symbol, data[i].valuta, data[i].isin, data[i].sektor, data[i].icb, 0);
	$("#tags").autocomplete({source: items});	


	
	/*
	Hämtar färsk information om varje aktie. 
	*/
   	function getStocksInfo(){
		$.getJSON("database.php",{action:'getStockInfo'})
			.done(function( data ) {
				console.log("DONE");

				$.each( data, function( i, item ) {
			        console.log(data[i]);

	        		//items.push(data[i].name);
					//stocks[data[i].y_symbol] = data[i];
					//nameDict[data[i].name] = data[i].y_symbol;
			    });
		      	
			})
		  	.fail(function( jqxhr, textStatus, error ) {
			    var err = textStatus + ", " + error;
			    console.log( "Request Failed: " + err );
			});




		//stocks[data.query.results.quote.Symbol].info = data.query.results.quote;
		//var temp = stocks[data.query.results.quote.Symbol];

		//$('#stockTable').append('<tr><td>'+temp.name+'</td><td>'+temp.symbol+'</td><td>'+temp.info.LastTradePriceOnly+'</td><td>'+temp.isin+'</td><td>'+temp.sektor+'</td><td>'+temp.icb+'</td></tr>');

		//console.log($('#stockTable tr').length);
		//$('#stockTable').trigger("update");
		//$("#stockTable").trigger("sorton",[[0,0]]);  

   	};
   	

  	

   	/*
	Ger förslag på aktier under nya möten. Lägger till aktieinformation till mötesprotokollet.  
   	*/
	$( "#tags" ).on( "autocompleteselect", function( event, ui ) {

   		var symbol = nameDict[ui.item.label];
   		var stock = stocks[symbol];

   		$('#meetingStock').append('<div class="meetStock"><h3>'+ui.item.label+' ('+stock.symbol+')</h3><ul><li>Utveckling i procent: '+stock.info.ChangeinPercent+'</li><li>Utveckling i SEK: '+stock.info.Change+'</li><li>Köp: '+stock.info.Bid+'</li><li>Sälj: '+stock.info.Ask+'</li><li>Senast: '+stock.info.LastTradePriceOnly+'</li><li>Högst: '+stock.info.DaysHigh+'</li><li>Lägst: '+stock.info.DaysLow+'</li><li>Antal: '+stock.info.Volume+'</li><li>Tid: '+stock.info.LastTradeTime+'</li></ul></div>');

	});



	/*
	Lägger till en aktie till databasen.
	*/
	function insertStock(name, symbol, ysymbol, valuta, isin, sektor, icb, owned){
		$.ajax({
	        type: "GET",
	        url: "database.php",
	        data: { action:'insertStock', name: name, symbol: symbol, ysymbol: ysymbol, valuta: valuta, isin: isin, sektor:sektor, icb: icb, owned:owned},
	        success: function(msg){
	            console.log(msg);
	        }
	    });
	};



	/*
	Lägger till aktiehistorik i databasen.
	*/
	function insertStockHistory(symbol, date, open, high, low, close, volume, adj_close){
		$.ajax({
	        type: "GET",
	        url: "database.php",
	        data: { action:'insertStockHistory', symbol:symbol, date:date, open:open, high:high, low:low, close:close, volume:volume, adj_close:adj_close},
	        success: function(msg){
	            console.log(msg);
	        }
	    });
	};
});

