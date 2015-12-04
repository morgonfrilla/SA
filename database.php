<?php 

	header("Content-type: text/html; charset=iso-8859-1"); 

	$con=mysqli_connect();
	mysqli_set_charset($con, 'utf8');

	// Check connection
	if (mysqli_connect_errno()) {
 	 echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}



	/*	
	 * Sätter in en ny aktie i databasen stocks.
	 */
	function insertStock($con, $name, $symbol, $ysymbol, $valuta, $isin, $sektor, $icb, $owned) {

   	 	$sql = "INSERT INTO stocks VALUES ('$name', '$symbol', '$ysymbol', '$valuta', '$isin', '$sektor', '$icb', '$owned');";
		$result = mysqli_query($con, $sql);

	}



	/*	
	 * Lägger till informationen i tabellen stockHistory.
	 */
	function insertStockHistory($con, $ysymbol, $date, $open, $high, $low, $close, $volume, $adj_close) {

   	 	$sql = "INSERT INTO stockHistory VALUES ('$ysymbol', '$date', '$open', '$high', '$low', '$close', '$volume', '$adj_close');";
		$result = mysqli_query($con, $sql);

	}



	/*	
	 * Hämtar historisk information för varje aktie och vidarbefodrar till insertStockHistory funktionen. 
	 * Symbol,	Date, Open,	High, Low, Close, Volume, Adj_Close 
	 */	
	function updateStockHistory($con) {


   	 	$symbolArray = array();

   	 	$sql = "SELECT Ysymbol, MAX(Date) , COUNT(Ysymbol) FROM stockHistory GROUP BY Ysymbol";
		$result = mysqli_query($con,$sql);
			
		while($row = mysqli_fetch_array($result)) {
			array_push($symbolArray, [$row['Ysymbol'],$row['MAX(Date)'],$row['COUNT(Ysymbol)']]);
		}


		//Datumintervall för historik
		$yesterday = new DateTime('NOW');
		$yesterday->modify('-1 day');
		$interval1 = new DateTime('NOW');
		$interval1->modify('-1 year');
		$interval2 = new DateTime('NOW');
		$interval2->modify('-2 year');
		$interval3 = new DateTime('NOW');
		$interval3->modify('-3 year');
		$dateInterval = array($interval3->format('Y-m-d'), $interval2->format('Y-m-d'), $interval1->format('Y-m-d'), $yesterday->format('Y-m-d'));


		foreach ($symbolArray as $symbol) {

			$maxDate = new DateTime($symbol[1]);

			
    		if($symbol[2]<780) {

    			echo "Get more history: ".$symbol[0]." count ".$symbol[2]."\n";

    			for ($x=0; $x<3; $x++) {

					$histData = rawurlencode("select * from yahoo.finance.historicaldata where symbol in ('".$symbol[0]."') and startDate='".$dateInterval[$x]."' and endDate='".$dateInterval[$x+1]."';");
					$url = 'http://query.yahooapis.com/v1/public/yql';

					$json = file_get_contents($url."?q=".$histData."&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys");
					$arr = json_decode($json,true);
					
					$quote = $arr['query']['results']['quote'];

					for($y=0; $y<count($arr['query']['results']['quote']); $y++) {

						$ysymbol = $quote[$y]['Symbol'];
						$date = $quote[$y]['Date'];
						$open = $quote[$y]['Open'];
						$high = $quote[$y]['High'];
						$low = $quote[$y]['Low'];
						$close = $quote[$y]['Close'];
						$volume = $quote[$y]['Volume'];
						$adj_close = $quote[$y]['Adj_Close'];

						insertStockHistory($con, $ysymbol, $date, $open, $high, $low, $close, $volume, $adj_close);
					}
					
				} 
    		}
    		

    		elseif($maxDate->format('Y-m-d')<$yesterday->format('Y-m-d')) {

				echo "Update stocks: ".$symbol[0]."-".$maxDate->format('Y-m-d')."<".$yesterday->format('Y-m-d')."\n";
    			
				$histData = rawurlencode("select * from yahoo.finance.historicaldata where symbol in ('".$symbol[0]."') and startDate='".$maxDate->format('Y-m-d')."' and endDate='".$yesterday->format('Y-m-d')."';");
				$url = 'http://query.yahooapis.com/v1/public/yql';

				$json = file_get_contents($url."?q=".$histData."&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys");
				$arr = json_decode($json,true);
					
				$quote = $arr['query']['results']['quote'];

				for($y=0; $y<count($arr['query']['results']['quote']); $y++) {

					$ysymbol = $quote[$y]['Symbol'];
					$date = $quote[$y]['Date'];
					$open = $quote[$y]['Open'];
					$high = $quote[$y]['High'];
					$low = $quote[$y]['Low'];
					$close = $quote[$y]['Close'];
					$volume = $quote[$y]['Volume'];
					$adj_close = $quote[$y]['Adj_Close'];

					insertStockHistory($con, $ysymbol, $date, $open, $high, $low, $close, $volume, $adj_close);
				}
			}
    	}	
    	
    	echo "updateStockHistory done"."\n";
	}


	/*
	Lägger till aktier som saknas i aktiehistoriken. 
	*/
	function addStockHistory($con) {


   	 	$symbolArray = array();

   	 	$sql = "SELECT Ysymbol FROM stocks WHERE Ysymbol NOT IN (SELECT Ysymbol FROM stockHistory GROUP BY Ysymbol);";
		$result = mysqli_query($con,$sql);
			
		while($row = mysqli_fetch_array($result)) {
			array_push($symbolArray, $row['Ysymbol']);
		}
		
		foreach ($symbolArray as $symbol) {

			echo "Adding ".$symbol." to stockHistory\n";

			for ($x=0; $x<3; $x++) {

				$histData = rawurlencode("select * from yahoo.finance.historicaldata where symbol in ('".$symbol."') and startDate='2013-08-28' and endDate='2014-08-28';");
				$url = 'http://query.yahooapis.com/v1/public/yql';

				$json = file_get_contents($url."?q=".$histData."&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys");
				$arr = json_decode($json,true);
				
				$quote = $arr['query']['results']['quote'];

				for($y=0; $y<count($arr['query']['results']['quote']); $y++) {

					$ysymbol = $quote[$y]['Symbol'];
					$date = $quote[$y]['Date'];
					$open = $quote[$y]['Open'];
					$high = $quote[$y]['High'];
					$low = $quote[$y]['Low'];
					$close = $quote[$y]['Close'];
					$volume = $quote[$y]['Volume'];
					$adj_close = $quote[$y]['Adj_Close'];

					insertStockHistory($con, $ysymbol, $date, $open, $high, $low, $close, $volume, $adj_close);
				}
				
			} 		
    	}	
    	echo "addStockHistory done"."\n";
    	
	}



	/*
	Hämtar färsk information om varje aktie och uppdaterar databasen. 
	*/
   	function updateStocksInfo($con){

   	 	$sql = "SELECT * FROM stocks;";
		$result = mysqli_query($con,$sql);

		
		$x = 0;
		$i = 0;
		$length = $result->num_rows;
		$symbolString = "";
		$symbolStringArray = [];
		
		while($row = mysqli_fetch_array($result)) {

			if($x >= 100){
				$symbolString .= $row['Ysymbol'];	
				array_push($symbolStringArray, $symbolString);
				$x = 0;
				$symbolString = "";
			}

			elseif($i+1 == $length) {
				$symbolString .= $row['Ysymbol'];
				array_push($symbolStringArray, $symbolString);
			}
			
			$symbolString .= $row['Ysymbol'].",";
			$x++;
		}

		

		$con2=mysqli_connect('192.168.1.5','SA','SAmysql2014','SA');
		mysqli_set_charset($con2, 'utf8');

		$date = new DateTime('NOW');

   		foreach($symbolStringArray as $symbolString) {
  			
			$histData = rawurlencode("select * from yahoo.finance.quotes where symbol in ('".$symbolString."');");
			$url = 'http://query.yahooapis.com/v1/public/yql';

			$json = file_get_contents($url."?q=".$histData."&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=");
			$arr = json_decode($json,true);
			
			$stockInfoArray = $arr['query']['results']['quote'];

	   		for($i=0;$i<count($stockInfoArray);$i++) {

	   			$s = $stockInfoArray[$i];

  				$sql2 = "UPDATE stockInfo SET Updated='".$date->format('Y-m-d H:i:s')."', Ask='".$s['Ask']."', AskRealtime='".$s['AskRealtime']."', AverageDailyVolume='".$s['AverageDailyVolume']."',Bid='".$s['Bid']."',BidRealtime='".$s['BidRealtime']."',`Change`='".$s['Change']."',ChangeFromFiftydayMovingAverage='".$s['ChangeFromFiftydayMovingAverage']."',ChangeFromTwoHundreddayMovingAverage='".$s['ChangeFromTwoHundreddayMovingAverage']."',ChangeFromYearHigh='".$s['ChangeFromYearHigh']."',ChangeFromYearLow='".$s['ChangeFromYearLow=']."',ChangePercentRealtime='".$s['ChangePercentRealtime']."',ChangeRealtime='".$s['ChangeRealtime']."',Change_PercentChange='".$s['Change_PercentChange']."',ChangeinPercent='".$s['ChangeinPercent']."',DaysHigh='".$s['DaysHigh']."',DaysLow='".$s['DaysLow']."',DividendPayDate='".$s['DividendPayDate']."',DividendShare='".$s['DividendShare']."',DividendYield='".$s['DividendYield']."',LastTradeDate='".$s['LastTradeDate']."',LastTradePriceOnly='".$s['LastTradePriceOnly']."',Open='".$s['Open']."',PercebtChangeFromYearHigh='".$s['PercebtChangeFromYearHigh']."',PercentChange='".$s['PercentChange']."',PercentChangeFromFiftydayMovingAverage='".$s['PercentChangeFromFiftydayMovingAverage']."',PercentChangeFromTwoHundreddayMovingAverage='".$s['PercentChangeFromTwoHundreddayMovingAverage']."',PercentChangeFromYearLow='".$s['PercentChangeFromYearLow']."',PreviousClose='".$s['PreviousClose']."',Volume='".$s['Volume']."',YearHigh='".$s['YearHigh']."',YearLow='".$s['YearLow']."'WHERE Ysymbol='".$s['Symbol']."';";
				mysqli_query($con2, $sql2);
			}

			mysqli_close($con2);
		}
   	}


   	/*
	Omvandlar array till Json
   	*/
   	function getStockInfo($con){
   		
   	 	$sql = "SELECT * FROM `stockInfo` JOIN stocks ON stockInfo.Ysymbol = stocks.Ysymbol;";
		$result = mysqli_query($con,$sql);

   		$jsonArray = [];

   		while($row = mysqli_fetch_array($result)) {
   			array_push($jsonArray, $row);
   		}
   		
   		var_dump($jsonArray);

		echo json_encode($jsonArray);
   	}




	if($_GET['action']) {
	    $action = $_GET['action'];
	    switch($action) {

	        case 'insertStock': 
                $name = $_GET['name'];
   	 			$symbol = $_GET['symbol'];
   	 			$ysymbol = $_GET['ysymbol'];
   	 			$valuta = $_GET['valuta'];
   	 			$isin = $_GET['isin'];
   	 			$sektor = $_GET['sektor'];
   	 			$icb = $_GET['icb'];
   	 			$owned = $_GET['owned'];
	        	insertStock($con, $name, $symbol, $ysymbol, $valuta, $isin, $sektor, $icb, $owned);
	        	break;

	        case 'insertStockHistory':
           	 	$ysymbol = $_GET['symbol'];
		   	 	$date = $_GET['date'];
		   	 	$open = $_GET['open'];
		   	 	$high = $_GET['high'];
		   	 	$low = $_GET['low'];
		   	 	$close = $_GET['close'];
		   	 	$volume = $_GET['volume'];
		   	 	$adj_close = $_GET['adj_close'];
	        	insertStockHistory($con, $ysymbol, $date, $open, $high, $low, $close, $volume, $adj_close);
	        	break;

            case 'updateStockHistory':
	        	updateStockHistory($con);
	        	break;

            case 'addStockHistory':
	        	addStockHistory($con);
	        	break;

            case 'updateStocksInfo':
	        	updateStocksInfo($con);
	        	break;

            case 'getStockInfo':
            	var_dump("Database getStockInfo");
	        	getStockInfo($con);
	        	break;

    	}
	}

	mysqli_close($con);
?>

