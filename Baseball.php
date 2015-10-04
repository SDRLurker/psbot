<?php
class Baseball
{
    protected $version = '0.1.0';

    protected $user_msg;
    protected $telegram;

    protected $translations;
    protected $conn;
    protected $language;

    protected $is_echo;

    /** Constructor
     *
     * @param string $user_msg : decoded JSON from Telegram
     * @param class  $telegram : Telegram Object
     */
    public function __construct($user_msg, $telegram, $is_echo = false) {
        include 'db.php';
        $this->user_msg = $user_msg;
        $this->telegram = $telegram;

        $this->is_echo = $is_echo;
        $this->language = 'en';

        $lang_content = file_get_contents('lang.json', true);
        $this->translations = json_decode($lang_content);

        $this->conn = new mysqli($server, $dbid, $dbpass, $dbname);
        if ($this->conn->connect_error) die($this->conn->connect_error);
    }
    public function __destruct() {
        $this->conn->close();
    }

    private function chat_id() {
        return $this->user_msg->{'message'}->{'chat'}->{'id'};
    }
    private function chat_name() {
        return $this->user_msg->{'message'}->{'chat'}->{'first_name'};
    }
    private function text() {
        return $this->user_msg->{'message'}->{'text'};
    }
    
    private function translation($nation, $field) {
        return $this->translations->{$nation}->{$field};
    }

    private function sendText($text){
        if($this->is_echo == true)
            echo $text;
        else   
            $this->telegram->sendMessage($this->chat_id(), $text);
    }

    private function proc_language($cmd) {
        if($this->text() == $this->translation('kr',$cmd))
            $language = 'kr';
        else if($this->text() == $this->translation('en',$cmd))
            $language = 'en';
        $this->language = $language;
        $stmt = $this->conn->prepare("UPDATE ps SET nation=? WHERE id=?");
        $stmt->bind_param("ss", $language, $this->chat_id());
        $result = $stmt->execute();
        $stmt->close();
        if (!$result) die ("UPDATE ps SET nation failed: " . $this->conn->error);

        $bot_msgs = $this->translations->{$language};
        return $bot_msgs;
    }
    private function start($pstbl) {
        $bot_msgs = $this->proc_language('startcmd');

        // The bot makes the secret number. 
        $now = microtime();
        $timestamps = explode(" ", $now);
        $timestamp = (double)$timestamps[0] + (double)$timestamps[1];
        srand ( (double)microtime()*1000000 );
        while(TRUE)
        {
            $thesame = 0;
            $digits = array();
            for($i=0;$i<3;$i++)
                array_push($digits, mt_rand(1,9));
            for($i=0;$i<2;$i++)
            {
                for($j=$i+1;$j<3;$j++)
                {
                    if($digits[$i] == $digits[$j])
                        $thesame = 1;
                }
            }
            if($thesame == 0)
                break;
        }
        for($que_num = 0, $i=0;$i<3;$i++)
            $que_num = $que_num + (pow(10,$i) * $digits[$i]);

        // The bot inserts or updates the user's game info.
        if(empty($pstbl)) 
        {
            $query = "INSERT INTO ps (id, que_num, start, nation) VALUES (?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssds", $this->chat_id(), $que_num, $timestamp, $this->language);
        }
        else
        {
            if($pstbl['start'] != NULL)
            {
                $this->sendText($bot_msgs->{'already'}); 
                return;
            }
            else
            {
                $query = "UPDATE ps SET que_num=?, start=? WHERE id=?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("sds", $que_num, $timestamp, $this->chat_id());
            }
        }
        $result = $stmt->execute();
        $stmt->close();
        if (!$result) die ($query."<br>UPDATE or INSERT que_num failed: " . $this->conn->error);

        $this->sendText($bot_msgs->{'start'}); 
    }
    private function rank($pstbl) {
        $bot_msgs = $this->proc_language('rankcmd');

        // The bot searchs for the player's ranking.
        if(empty($pstbl)) 
        {
            $answer = sprintf($bot_msgs->{'norank'}, $this->chat_name());
        }
        else 
        {
            if($pstbl['record'] != NULL)
            {
                $query = "SELECT count(*) FROM ps WHERE record < ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $pstbl['record']);
                $result = $stmt->execute();
                if (!$result) {  $stmt->close(); die ("SELECT count(*) failed: " . $this->conn->error); } 
                $result = $stmt->get_result(); $stmt->close();
                $row = $result->fetch_array(MYSQLI_NUM);
                $ranking = (int)$row[0]+ 1;
                $answer = sprintf($bot_msgs->{'rank'}, $this->chat_name(), $pstbl['record'], $ranking);
            }
            else
            {
                $answer = sprintf($bot_msgs->{'norank'}, $this->chat_name());
            }
        }

        $this->sendText($answer); 
    }
    private function check_number($pstbl) {
        if(empty($pstbl)) 
            $bot_msgs = $this->translations->{'en'};
        else   
            $bot_msgs = $this->translations->{$pstbl['nation']};

        // The bot checks the validations of the player's number.
        $valid = 1;
        for($i=0;$i<3;$i++) {
            if($this->text()[$i]<'1' || $this->text()[$i]>'9')
                $valid = 0;
        }
        for($i=0;$i<2;$i++) {
            for($j=$i+1;$j<3;$j++) {
                if($this->text()[$i] == $this->text()[$j]) {
                    $valid = 0;
                    break;  
                }
            }
        }

        if($valid == 1)
        {     
            $question = 1;    
            if(empty($pstbl)) 
            {
                $question = 0;
            }
            else
            {
                if($pstbl['que_num'] == NULL)
                    $question = 0;
                else
                    $que_num = $pstbl['que_num'];                                
            }  
            if($question == 0)
            {
                $this->sendText($bot_msgs->{'no'});
                return;
            }

            $strike = 0;
            $ball = 0;
            for($i=0;$i<3;$i++) {
                $pt_num = strpos($que_num, $this->text()[$i]);
                if($pt_num !== false) {
                    if($pt_num == $i)
                        $strike++;
                    else
                        $ball++;
                }
            }
            $answer = '';
            if($strike>0)
                $answer = sprintf($bot_msgs->{'s'}, $strike);
            if($ball>0)
                $answer = $answer." ".sprintf($bot_msgs->{"b"}, $ball);
            if($strike==0 && $ball==0)
                $answer = $bot_msgs->{"o"};
            else if($strike<3)
                $answer = $answer;
            else
            {
                $now = microtime();
                $timestamps = explode(" ", $now);
                $timestamp = (double)$timestamps[0] + (double)$timestamps[1];
                $diff = $timestamp - (double)$pstbl['start'];

                $query = "UPDATE ps SET start = NULL, que_num = NULL WHERE id=?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $this->chat_id());
                $result = $stmt->execute();
                if (!$result) die ("UPDATE ps SET start, que_num failed: " . $this->conn->error);

                $query = "UPDATE ps SET record=?  WHERE id=? AND (record > ? OR record IS NULL )";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("sss", $diff, $this->chat_id(), $diff);
                $result = $stmt->execute();
                if (!$result) die ("UPDATE ps SET record failed: " . $this->conn->error);

                $answer = $answer.sprintf($bot_msgs->{'solve'}, (int)$diff);
            }
            $this->sendText($answer);
        }
        else
        {
            $this->sendText($bot_msgs->{'wrong'});
        }
    }

    private function help($pstbl) {
        $bot_msgs = $this->proc_language('helpcmd');
        $this->sendText($bot_msgs->{'help'});
    }	

    public function process() {
        $query = "SELECT id, start, que_num, record, nation FROM ps WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->chat_id());
        $result = $stmt->execute();
        if (!$result){ $stmt->close(); die ("SELECT from ps failed: " . $this->conn->error); }
        $result = $stmt->get_result(); $stmt->close();

        $row = $result->fetch_array(MYSQLI_BOTH);
        mysqli_free_result($result);

        switch($this->text()) {
            case '/'.$this->translation('kr','startcmd'):
            case '/'.$this->translation('en','startcmd'):
                $this->user_msg->{'message'}->{'text'} = 
                    substr($this->text(), 1);
            case $this->translation('kr','startcmd'):
            case $this->translation('en','startcmd'):
                $this->start($row); 
                break;  

            case '/'.$this->translation('kr','rankcmd'):
            case '/'.$this->translation('en','rankcmd'):
                $this->user_msg->{'message'}->{'text'} = 
                    substr($this->text(), 1);
            case $this->translation('kr','rankcmd'):
            case $this->translation('en','rankcmd'):
                $this->rank($row); 
                break; 
            case '/'.$this->translation('kr','helpcmd'):
            case '/'.$this->translation('en','helpcmd'):
                $this->user_msg->{'message'}->{'text'} = 
                    substr($this->text(), 1);
            case $this->translation('kr','helpcmd'):
            case $this->translation('en','helpcmd'):
                $this->help($row); 
                break; 
        }
        if(strlen($this->text()) == 3 && intval($this->text()) != 0)
        {
            $this->check_number($row);
        }
    }
}
?>
