<?php
session_start();
$userInput1 = $_POST['us1'];
$userInput2 = $_POST['us2'];

/*
** Makes a weighted associative array of all the words in each tweet
** Key, Value => Word, Weight
*/
function makeWeightedArray($textArr) {
    
    $count = count($textArr);

    $weightedArr = array();

    for ($i = 0; $i < $count; $i++) {

        $countInner = count($textArr[$i]);
        $weight = $textArr[$i][$countInner-1];

        for ($j = 0; $j < $countInner - 1; $j++) {

            $word = $textArr[$i][$j];

            if (array_key_exists($word, $weightedArr) && $word != '') {
                $weightedArr[$word] += $weight;
            }
            else {
                $weightedArr[$word] = $weight;
            }
        }
    }

    arsort($weightedArr);
    $weightedArr = array_slice($weightedArr, 0, 500); //Limit to top 100 words
    return $weightedArr;
    
} //makeWeightedArray()

/*
** Makes a two-dimensional array:
**     -Top level array is array of tweets
**     -Sub-arrays are all the individual words in each tweet
*/
function makeTweetArray($response) {
    //Create a new array from the JSON response
    $arr = array();
    $arr = json_decode($response, true);
    $arrLength = count($arr);
    
    //Creating an array to hold all the tweets
    $textArr = array();

    //Go through each tweet and make a sub-array of words in each tweet
    for ($i = 0; $i < $arrLength; $i++) {

        //Create a sub-array to hold the individual words of the tweet
        $textLine = array(); 
        $textLine = explode(" ", $arr[$i]["text"]);
        $textLineArrLength = count($textLine);

        //Clean each word
        for ($j = 0; $j < $textLineArrLength; $j++) {
            $clear = trim(
                preg_replace('/ +/', '', 
                preg_replace('/[^A-Za-z0-9 ]/', '', 
                urldecode(html_entity_decode(strip_tags($textLine[$j])))))
            );
            $clear = preg_replace('/[`~!$%^&*()_+-={}[]|\,.<>"\']/', '', $clear);

            $textLine[$j] = $clear;
        }

        array_push($textLine, $arr[$i]["retweet_count"]);
        array_push($textArr, $textLine);
    }
    
    return $textArr;
} //makeTweetArray()

//Include the Twitter API php document
require_once('TwitterAPIExchange.php');

//Set all Twitter API tokens
$settings = array(
    "oauth_access_token" => "2392968917-ZDBrtQQxW0tzcvmM1iGSPrPCiOv8mruAtJ6kJXO",
    "oauth_access_token_secret" => "WpB70nL1KkyrVpCjTxpLhYisvhFn23f7VBPeJsHGZBwwx",
    "consumer_key" => "vk9lMuXDIkQJhORlI7bqNsDuG",
    "consumer_secret" => "9wx9g0dQXbdasyof0Bj6kxTAob5aY3uWKaZWwK7SzCLyQ9opIN"
);

//Set indico API key
$indicoKey = "5f5ed36c02ff4af77842188d58bfb2b3";

//Create new Twitter object so we can use the API
$twitter = new TwitterAPIExchange($settings);

//Get response from URL 1
$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name=' . $userInput1 . '&count=200';
$requestMethod = 'GET';
$response = $twitter->setGetfield($getfield)
    ->buildOauth($url, $requestMethod)
    ->performRequest();

//Get response from URL 2
$url2 = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield2 = '?screen_name=' . $userInput2 . '&count=200';
$requestMethod2 = 'GET';
$response2 = $twitter->setGetfield($getfield2)
    ->buildOauth($url2, $requestMethod2)
    ->performRequest();

//Print a pretty version of the array
//echo "<pre>"; print_r(json_decode($response)); echo "</pre>";

//The first output
$textArr = makeTweetArray($response);
$weightedArr = makeWeightedArray($textArr);

//Set profilePic and name variable for first output
$arr = array();
$arr = json_decode($response, true);
$profilePic = $arr[0]['user']['profile_image_url'];
$us1 = $arr[0]['user']['name'];

//The second output
$textArr2 = makeTweetArray($response2);
$weightedArr2 = makeWeightedArray($textArr2);

//Set profilePic variable and name for second output
$arr2 = array();
$arr2 = json_decode($response2, true);
$profilePic2 = $arr2[0]['user']['profile_image_url'];
$us2 = $arr2[0]['user']['name'];

//Set path for our word bank text files
$posFile = "pos.txt";
$negFile = "neg.txt";
$relFile = "rel.txt";

//Load the files into an array for processing
$positivityArr  = file($posFile, FILE_IGNORE_NEW_LINES);
$negativityArr  = file($negFile, FILE_IGNORE_NEW_LINES);
$religiosityArr = file($relFile, FILE_IGNORE_NEW_LINES);

//Need unweighted arrays for comparisons
$a1 = array_keys($weightedArr);
$a2 = array_keys($weightedArr2);

//Calculate positivity ratings
$resultsPos1 = array_intersect($positivityArr, $a1);
$resultsNeg1 = array_intersect($negativityArr, $a1);
$resultsPos2 = array_intersect($positivityArr, $a2);
$resultsNeg2 = array_intersect($negativityArr, $a2);

//Calculate religiosity rating
$resultsRel1 = array_intersect($religiosityArr, $a1);
$resultsRel2 = array_intersect($religiosityArr, $a2);

//Count of how many positive/negative words were used
$posCount1 = count($resultsPos1);
$negCount1 = count($resultsNeg1);
$posCount2 = count($resultsPos2);
$negCount2 = count($resultsNeg2);

//Count of how many religious words were used
$relCount1 = count($resultsRel1);
$relCount2 = count($resultsRel2);

$popularity1 = array_sum($weightedArr);
$popularity2 = array_sum($weightedArr2);

//echo $posCount1 . " " . $negCount1 . " " . $posCount2 . " " . $negCount2;

//$positivityRatio1 = $posCount1 / $negCount1;
//$positivityRatio2 = $posCount2 / $negCount2;

?>

<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Analysis</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <style>
        
        body { 
            width: 100%;
            background-color: lightblue;
            overflow-x: hidden;
        }
        
        .container {
            margin: 0 auto;
            text-align: center;
        }
        
        .col {
            background-color: white;
            
            border-radius: 5px;
            box-shadow: 5px 5px 5px #64A0DD;
            
            display: inline-block;
            
            width: 400px;
            margin: 20px;
            padding: 10px;
            text-align: left;
        }
        
        img.center {
            display: block;
            margin: 0 auto;
        }
        
    </style>
    
</head>

<body>

    <div class="container">
            <div class="col" id="tweets1"></div>
            <div class="col" id="tweets2"></div>
    </div>

    <script>
        
        function makeCol(wa, tb) {
            //Make this method
        }
        
        function round(value, decimals) {
          return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
        }
        
        $(document).ready(function(){
            //Set positivity/religiosity ratio
            var p1 = parseInt("<?php echo ($posCount1); ?>");
            var n1 = parseInt("<?php echo ($negCount1); ?>");
            var p2 = parseInt("<?php echo ($posCount2); ?>");
            var n2 = parseInt("<?php echo ($negCount2); ?>");
            var r1 = parseInt("<?php echo ($relCount1); ?>");
            var r2 = parseInt("<?php echo ($relCount2); ?>");
            var posnegtotal1 = p1 + n1;
            var posnegtotal2 = p2 + n2;
            var relRating1 = r1 / 17 * 100;
            var relRating2 = r2 / 17 * 100;
            
            if (posnegtotal1 != 0) {
                var posRating1 = p1 / posnegtotal1 * 100;
            } else { 
                posnegtotal1 = "N/A";
            }
            
            if (posnegtotal2 != 0) { 
                var posRating2 = p2 / posnegtotal2 * 100;
            } else { 
                posnegtotal2 = "N/A";
            }
            
            var popularity1 = parseInt("<?php echo ($popularity1); ?>");
            var popularity2 = parseInt("<?php echo ($popularity2); ?>");
            
            posRating1 = round(posRating1, 2);
            posRating2 = round(posRating2, 2);
            relRating1 = round(relRating1, 2);
            relRating2 = round(relRating2, 2);
            
            console.log("p1: " + p1);
            console.log("n1: " + n1);
            console.log("p2: " + p2);
            console.log("n2: " + n2);
            console.log("posnegtotal1: " + posnegtotal1);
            console.log("posnegtotal2: " + posnegtotal2);
            console.log("posRating1: " + posRating1);
            console.log("posRating2: " + posRating2);
            
            //Set usernames
            var us1 = "<?php echo ($us1);  ?>";
            var us2 = "<?php echo ($us2);  ?>";
            
            //Set elements
            var tweetsBox1 = document.getElementById("tweets1");
            var tweetsBox2 = document.getElementById("tweets2");

            //Set weighted arrays
            var weightedArray1 = <?php echo (json_encode($weightedArr));  ?>;
            var weightedArray2 = <?php echo (json_encode($weightedArr2)); ?>;
            
            var profPixTxt1 = "<img src=\"" + "<?php echo $profilePic; ?>"  + "\" class=\"center\">";
            var profPixTxt2 = "<img src=\"" + "<?php echo $profilePic2; ?>" + "\" class=\"center\">";
            
            tweetsBox1.innerHTML += profPixTxt1 +  
                "<h2><center>" + us1 + "</center></h2>" + 
                
                "<b>Positivity Rating:</b>" +
                "<div class=\"w3-light-grey w3-round\"><div class=\"w3-container w3-round w3-blue\" style=\"width:" + posRating1 + "%\">" + posRating1 + "%</div></div>" +
                
                "<b>Religiosity Rating:</b>" +
                "<div class=\"w3-light-grey w3-round\"><div class=\"w3-container w3-round w3-blue\" style=\"width:" + relRating1 + "%\">" + relRating1 + "%</div></div>" +
                
                "<br>" + "<b>Impact: </b>" + popularity1 + "<br>" + 

                "<br>" + "<b>Popular Words:</b>" + "<br>";
            
            tweetsBox2.innerHTML += profPixTxt2 + 
                "<h2><center>" + us2 + "</center></h2>" + "<b>Positivity Rating:</b>" +
                
                "<div class=\"w3-light-grey w3-round\"><div class=\"w3-container w3-round w3-blue\" style=\"width:" + posRating2 + "%\">" + posRating2 + "%</div></div>" +
                
                "<b>Religiosity Rating:</b>" +
                "<div class=\"w3-light-grey w3-round\"><div class=\"w3-container w3-round w3-blue\" style=\"width:" + relRating2 + "%\">" + relRating2 + "%</div></div>" +
                
                "<br>" + "<b>Impact: </b>" + popularity2 + "<br>" + 
                
                "<br>" + "<b>Popular Words:</b>" + "<br>";
            
            $.each(weightedArray1, function(key, value) {
                tweetsBox1.innerHTML += (key + ", " + value);
                tweetsBox1.innerHTML += "<br>";
            });
            
            $.each(weightedArray2, function(key, value) {
                tweetsBox2.innerHTML += (key + ", " + value);
                tweetsBox2.innerHTML += "<br>";
            });

        });
    </script>
    
</body>
</html>