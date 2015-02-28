<style type="text/css">
    body {
        font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
        font-size: 13px;
    }
</style>
<?php          
        //function GetAccounts() {
        //Fetching values from Server
        $managerLogin = 431;
        $managerPassword = "wrfx123";
		$managerAddress = "185.64.248.35:443";
        $ReportingDate = "28/02/2015 11:11:45";			
        $uri = "http://api.4xsolutions.com/Mt4manager/Accounts/Get/";       

        // Request Parameters
        $data = array(
            "ServerLogin" => "431",
            "ServerPassword" => "wrfx123",
            "ServerAddress" => "185.64.248.35",
            "ServerPort" => 443
        );
        
        // uncomment to view request parameters
        /*print('<pre>');
        echo 'Request: ';
        print_r($data);
        print('</pre>');*/

        // fetching response from server
        $response = post_api($data, $uri);
        
        $apiResponse = array();
        $cnt = 0;
        foreach ($response as $key => $value) {
            $apiResponse[$cnt]['Login']=$value['Login'];
            $apiResponse[$cnt]['Name']=$value['Name'];
            $apiResponse[$cnt]['Currency']=$value['Currency'];
            $apiResponse[$cnt]['Balance']=$value['Balance'];
            $apiResponse[$cnt]['EmailAddress']=$value['EmailAddress'];
            $cnt++;
        }

        // database connection parameters
        $username = "root";
        $password = "";
        $hostname = "localhost";         
        $dbhandle = mysql_connect($hostname, $username, $password) or die("Unable to connect to MySQL");        

        // selecting database
        $selected = mysql_select_db("fx",$dbhandle) or die("Could not select database");

        //execute the SQL query and return records
        $result = mysql_query("SELECT email, activeStatus FROM users");        
        $users = array();
        $noOfRecordMatches = 0;
        $dbUserCount = 0;

        while ($row = mysql_fetch_array($result)) {                                             
            if($row['activeStatus']==0){
                $tradingStatus = 'Account initiation';
            } else if($row['activeStatus']==1){
                $tradingStatus = 'Account Opened';
            }

            foreach ($apiResponse as $key => $value) {
                if($apiResponse[$key]['EmailAddress']==$row['email']){
                    echo '<br/><b>Matches:</b> '.$row['email'].' == '.$apiResponse[$key]['EmailAddress'];         
                    $login = $apiResponse[$key]['Login'];                    
                    $currency = $apiResponse[$key]['Currency'];
                    $balance = $apiResponse[$key]['Balance'];                   
                    //echo "UPDATE users SET login='$login',balance='$balance',tradingStatus='$tradingStatus',curency='$currency' where email = '".$row['email']."'";
                    $result = mysql_query("UPDATE users SET login='$login',balance='$balance',tradingStatus='$tradingStatus',curency='$currency' where email = '".$row['email']."'");   
                    //echo ($result==1)?'  <i><b>record updated</b></i>':'  <i><b>Not updated</b></i>';
                    /*if($result==1){
                        echo '  <i><b>record updated</b></i>';
                    }*/
                    $noOfRecordMatches++;
                    break;                    
                }
            }
            $dbUserCount++;
        }

        echo '<br/><b> Total Number of users in Tradeland database: </b>'.$dbUserCount;
        echo '<br/><b> Total Number of Users in MT4 API: </b>'.$noOfRecordMatches;
        echo '<br/><b> No of user matches: </b>'.$noOfRecordMatches;
        
    function post_api($data, $uri)
    {
        $ch = curl_init($uri);

        $data_string = json_encode($data); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt( $ch , CURLOPT_SSL_VERIFYHOST , false );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))
        );
        
        //die($data_string);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = json_decode($result, true); 
        $response['httpcode'] = $httpcode;

        curl_close($ch);
        return($response); 
    }   
?>