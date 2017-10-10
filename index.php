<?php 
    session_start();
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
            width: 80%;
            padding: 20px;
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
    <br>
    <div class = "container">
        <b>Tweet Analyzer</b>
        
        <form id="loginform" action="getTweet.php" method="post">
            <div class="col">@<input name="us1" type="text" id="us1" size="8" /> 
                <strong>Username 1</strong>
            </div>

            <div class="col">@<input name="us2" type="text" id="us2" size="8" /> 
                <strong>Username 2</strong>
            </div>

            <div><input type="submit" value="Compare!" id="cmp" /></div>
        </form>
        
    </div>
</body>
    
</html>