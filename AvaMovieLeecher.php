<?php

#~~~~~~~ Var Set ~~~~~~~#
define('Account', '-- Username -- :-- Password --');
#~~~~~~~ Var Set ~~~~~~~#

#--------- GetPageID ---------#

if(isset($_REQUEST['page_url']))// GetPageID
{
    $link = $_REQUEST['page_url'];
    $GetSource = file_get_contents($link);
    preg_match('/data-post_id="([^"]+)"/', $GetSource, $matches);
    $MovieID =  $matches[1];

    $Username = explode(":" , Account)[0];
    $Password = explode(":" , Account)[1];

    f_AvaMovieLeecher($MovieID,$Username,$Password);
}

#--------- DigiMoviez-Leecher ---------#

function f_AvaMovieLeecher($MovieID , $Username , $Password)
{

    #--------------- LOGIN ---------------#

    $REQ_LOGIN = curl_init();

    curl_setopt($REQ_LOGIN, CURLOPT_URL, 'https://cifana.fun/api-url/app/1/login');
    curl_setopt($REQ_LOGIN, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($REQ_LOGIN, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($REQ_LOGIN, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($REQ_LOGIN, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($REQ_LOGIN, CURLOPT_HTTPHEADER, array('user-agent: Dart/3.3 (dart:io)', 'content-type: application/json', 'accept: application/json', 'host: cifana.fun'));
    curl_setopt($REQ_LOGIN, CURLOPT_POSTFIELDS, "{\"username\":\"$Username\",\"password\":\"$Password\"}");
    curl_setopt($REQ_LOGIN, CURLOPT_TIMEOUT, 30);
    // curl_exec($REQ_LOGIN);
   $RES_LOGIN_JSON = json_decode(curl_exec($REQ_LOGIN), TRUE);

    if($RES_LOGIN_JSON['stat'] == "ok")//~ Get Sub
    {
        #--------------- Get SUb ---------------#

        $USER_ID = $RES_LOGIN_JSON['user_id'];
        $TOKEN = $RES_LOGIN_JSON['token'];

        $REQ_SUB = curl_init();

        curl_setopt($REQ_SUB, CURLOPT_URL, 'https://cifana.fun/api-url/app/1/user_info?token='.$TOKEN.'&user_id='.$USER_ID);
        curl_setopt($REQ_SUB, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($REQ_SUB, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($REQ_SUB, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($REQ_SUB, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($REQ_SUB, CURLOPT_HTTPHEADER, array('user-agent: Dart/3.3 (dart:io)', 'content-type: application/json', 'accept: application/json', 'host: cifana.fun'));
        curl_setopt($REQ_SUB, CURLOPT_POSTFIELDS, "{\"token\":\"$TOKEN\",\"user_id\":\"$USER_ID\"}");
        curl_setopt($REQ_SUB, CURLOPT_TIMEOUT, 30);
        // curl_exec($REQ_SUB);
        $RES_SUB_JSON = json_decode(curl_exec($REQ_SUB), TRUE);

        if($RES_SUB_JSON['is_subscription'] == "1")
        {
            #--------------- Get Movie Info ---------------#

            $REQ_GET_MS = curl_init();

            curl_setopt($REQ_GET_MS, CURLOPT_URL, 'https://cifana.fun/api-url/app/1/single?token='.$TOKEN.'&user_id='.$USER_ID);
            curl_setopt($REQ_GET_MS, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($REQ_GET_MS, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($REQ_GET_MS, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($REQ_GET_MS, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($REQ_GET_MS, CURLOPT_HTTPHEADER, array('user-agent: Dart/3.3 (dart:io)', 'content-type: application/json', 'accept: application/json', 'host: cifana.fun'));
            curl_setopt($REQ_GET_MS, CURLOPT_POSTFIELDS, "{\"id\":\"$MovieID\",\"token\":\"$TOKEN\",\"user_id\":\"$USER_ID\"}");
            curl_setopt($REQ_GET_MS, CURLOPT_TIMEOUT, 30);
            //curl_exec($REQ_GET_MS);
            $RES_MS_JSON = json_decode(curl_exec($REQ_GET_MS), TRUE);

            $MOVIE_TITLE_EN = $RES_MS_JSON['title_en'];
            $MOVIE_TITLE_FA = $RES_MS_JSON['title_fa'];
            $MOVIE_POSTER = $RES_MS_JSON['poster'];
            $MOVIE_TYPE = $RES_MS_JSON['type'];
            $MOVIE_YEAR = $RES_MS_JSON['release'];
            $MOVIE_GENRE = $RES_MS_JSON['genre'][0]['text'];
            $MOVIE_COUNTRY = $RES_MS_JSON['country'][0]['text'];
            $MOVIE_TIME = $RES_MS_JSON['time'];
            $MOVIE_IMDB = $RES_MS_JSON['imdb_rate'];
            $MOVIE_DIRECTOR = $RES_MS_JSON['director'][0]['text'];
            $MOVIE_DUB = $RES_MS_JSON['dub'];

            if($MOVIE_TYPE == "movie")
            {
                #--------------- Get_Download_link(Movie) ---------------#

                $dl_Movie_Label = [];$dl_Movie_Quality = [];
                $dl_Movie_Size = [];$dl_Movie_Encoder = [];
                $dl_Movie_Sound_Link = [];$dl_Movie_Link = [];

                $index = 0;

                $hasDub = isset($RES_MS_JSON['dl']['movie']['dub']['items']) && is_array($RES_MS_JSON['dl']['movie']['dub']['items']);
                $hasSub = isset($RES_MS_JSON['dl']['movie']['sub']['items']) && is_array($RES_MS_JSON['dl']['movie']['sub']['items']);

                if ($hasDub) {
                    $dl_Movie_Dub_Count = count($RES_MS_JSON['dl']['movie']['dub']['items']);
                    for ($i = 0; $i < $dl_Movie_Dub_Count; $i++) {
                        $dl_Movie_Label[$index] = $RES_MS_JSON['dl']['movie']['dub']['title'] . " (Dub)";
                        $dl_Movie_Quality[$index] = $RES_MS_JSON['dl']['movie']['dub']['items'][$i]['quality'];
                        $dl_Movie_Size[$index] = $RES_MS_JSON['dl']['movie']['dub']['items'][$i]['size'];
                        $dl_Movie_Encoder[$index] = $RES_MS_JSON['dl']['movie']['dub']['items'][$i]['encoder'];
                        $dl_Movie_Sound_Link[$index] = $RES_MS_JSON['dl']['movie']['dub']['items'][$i]['sound'];
                        $dl_Movie_Link[$index] = $RES_MS_JSON['dl']['movie']['dub']['items'][$i]['url'];
                        $index++;
                    }
                }

                if ($hasSub) {
                    $dl_Movie_Sub_Count = count($RES_MS_JSON['dl']['movie']['sub']['items']);
                    for ($i = 0; $i < $dl_Movie_Sub_Count; $i++) {
                        $dl_Movie_Label[$index] = $RES_MS_JSON['dl']['movie']['sub']['title'] . " (Sub)";
                        $dl_Movie_Quality[$index] = $RES_MS_JSON['dl']['movie']['sub']['items'][$i]['quality'];
                        $dl_Movie_Size[$index] = $RES_MS_JSON['dl']['movie']['sub']['items'][$i]['size'];
                        $dl_Movie_Encoder[$index] = $RES_MS_JSON['dl']['movie']['sub']['items'][$i]['encoder'];
                        $dl_Movie_Sound_Link[$index] = null;
                        $dl_Movie_Link[$index] = $RES_MS_JSON['dl']['movie']['sub']['items'][$i]['url'];
                        $index++;
                    }
                }

                #~~~~ Movie Json ~~~~#

                echo(json_encode(array(

                    'code' => http_response_code(),
                    'message' => 'success' ,
                    'developer' => 'AGC007',

                    'data' =>   array(
                        'MovieName' => $MOVIE_TITLE_EN ,
                        'MovieNameFA' => $MOVIE_TITLE_FA,
                        'isSeries' => $MOVIE_TYPE ,
                        'MovieTime' => $MOVIE_TIME,
                        'MovieYear' => $MOVIE_YEAR ,
                        'MovieGenre' => $MOVIE_GENRE ,
                        'MovieCountry' => $MOVIE_COUNTRY ,
                        'MoviePoster' => $MOVIE_POSTER ,
                        'MovieIMDB' => $MOVIE_IMDB ,
                        'MovieDirector' => $MOVIE_DIRECTOR ,

                        'dl' => array(
                            'DL_Movie_Label' => $dl_Movie_Label,
                            'DL_Movie_Quality' => $dl_Movie_Quality ,
                            'DL_Movie_Size' => $dl_Movie_Size ,
                            'DL_Movie_Encoder' => $dl_Movie_Encoder ,
                            'DL_Movie_Sound' => $dl_Movie_Sound_Link ,
                            'DL_Movie_Link' => $dl_Movie_Link ,
                            'Developer' => "AGC007"
                        )))));

                #~~~~ Movie Json ~~~~#
            }
            elseif ($MOVIE_TYPE == "series")
            {
                #--------------- Get_Download_link(Series) ---------------#

                $SERIES_SEASONS = count($RES_MS_JSON['dl']['series']);

                #~~~~~ HTML SOURCE ~~~~~#
                ?>

                <html style="text-align: center;background-color: black; color:white;background-image: url('https://agc007.top/AGC007/Robot/KingMovieLeecher/KingMovieService/backiee-252055.jpg');" >
                <title>Ava Movie [SD] By AGC007</title>

                <img style="height: 300px;width: 300px; border-radius:30px; margin-bottom:8px;" src=<?php echo($MOVIE_POSTER)  ?>>
                </br>
                <a style="background-color:darkslategrey;">- SerialName : <?php echo($MOVIE_TITLE_EN."(".$MOVIE_YEAR.")") ?> -</a>
                </br>
                <a style="background-color:darkslategrey;">- SerialDirector : <?php echo($MOVIE_DIRECTOR) ?> -</a>
                </br>
                <a style="background-color:darkslategrey;">- SerialIMDB : <?php echo($MOVIE_IMDB) ?> -</a>
                </br>
                <a style="background-color:darkslategrey;">- SerialSeasons : <?php echo($SERIES_SEASONS) ?> -</a>
                </br>
                </html>

                <?php

                #~~~~~ HTML SOURCE ~~~~~#

                //$dl_SERIES_Count =  count($RES_MS_JSON['dl']['series']);

                for($A=0; $A <= $SERIES_SEASONS - 1; $A++)//Go To List & Get DATA
                {
                    $dl_SERIES_Count_link =  count($RES_MS_JSON['dl']['series']["0".$A+1]['items']);

                    for($AA=0; $AA <= $dl_SERIES_Count_link - 1; $AA++)//Go To List & Get DATA
                    {

                    echo "</br>";

                    echo $dl_Series_Season[$A] = "Season  " . $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['season'] ." - ";
                    echo $dl_Series_Episode[$A] = "Last Episode : " . $RES_MS_JSON['dl']['series']["0".$A+1]['count'];

                    echo "</br>";
                    echo $dl_Series_Quality[$A] = "Quality : " . $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['quality'];
                    echo "</br>";
                    echo $dl_Series_Size[$A] = "Size : " . $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['size'];
                    echo "</br>";

                    $TypeM = $RES_MS_JSON['dl']['series']["0" . $A + 1]['items'][$AA]['tag'] == "dub" ? "دوبله" : "زیرنویس";

                    echo $dl_Series_Encoder[$A] = "Subtype : " . $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['subtype'] . "(". $TypeM .")";
                    echo "</br>";

                    $dl_SERIES_Episode_Count =  count($RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['episodes']);

                    for($B=0; $B <= $dl_SERIES_Episode_Count - 1;$B++)//Go To Download Episode List & Res
                    {
                        $CC = $B; $CC+=1;
                        $FF = $A; $FF+=1;
                        echo $dl_Series_Episode_Part[$B] = $CC.":";

                        //$dl_Series_Episode_Source[$B] = $RES_SERIES_DOWN_LINK_JSON['result']['download']["s".$A+1][$AA]['link'][$B]['source'];

                        //if($dl_Series_Episode_Source[$B] == null)
                        //{
                        $dl_Series_Episode_Source[$B] = "Download Link - $FF - ".$CC;
                        //}

                        $dl_Series_Episode_Link[$B] = $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['episodes'][$B]['url'];
                        $dl_Series_Episode_Sound_Link[$B] = $RES_MS_JSON['dl']['series']["0".$A+1]['items'][$AA]['episodes'][$B]['sound'];

                        ?>
                        <a style="color:bisque;" href="<?php echo $dl_Series_Episode_Link[$B]; ?>"><?php echo $dl_Series_Episode_Source[$B]; ?> </a>
                        </br>
                        <?php

                        if( $RES_MS_JSON['dl']['series']["0" . $A + 1]['items'][$AA]['tag'] == "dub")
                        {
                            ?>
                            <a style="color: aqua;" href="<?php echo $dl_Series_Episode_Sound_Link[$B]; ?>"><?php echo "Sound Episode" ?> </a>

                        </br>

                        <?php
                        }
                    }
                    }
                }
                echo("</br> ~ Developer : AGC007 ~");
            }
        } else {
            echo(json_encode(array(
                'code' => '403',
                'message' => 'Account Subscription Error' ,
                'developer' => 'AGC007',
            )));
        }
    } else {
        echo(json_encode(array(
            'code' => '404',
            'message' => 'Account Login Error' ,
            'developer' => 'AGC007',
        )));
    }
}
?>