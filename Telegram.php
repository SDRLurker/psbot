<?php
include 'TelegramException.php';
class Telegram
{
    protected $version = '0.1.0';
    protected $api_key = '';	
    protected $bot_name = '';

    protected $BASE_URL = '';	

    /** Constructor
     *
     * @param string $api_key
     * @param string $bot_name
     */
    public function __construct($api_key, $bot_name)
    {
        if (empty($api_key)) {
            throw new TelegramException('API KEY not defined!');
        }
        if (empty($bot_name)) {
            throw new TelegramException('Bot Username not defined!');
        }
        $this->api_key = $api_key;
        $this->bot_name = $bot_name;

	$this->BASE_URL = 'https://api.telegram.org/bot'.$api_key.'/';
    }
	
    private function setCommand($com, $array)
    {
        $url = $this->BASE_URL.$com;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);

        //$curlConfig[CURLOPT_POSTFIELDS] = $array;
        $curlConfig[CURLOPT_POSTFIELDS] = http_build_query($array);
        curl_setopt_array($ch, $curlConfig);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_exec($ch);

        curl_close($ch);
        return array('status' => $status, 
            'result' => $result);
    }
    public function setWebhook($url)
    {
        $result = $this->setCommand("setWebhook", array('url' => $url));
	return $result;
    }
    public function sendMessage($chat_id, $text)
    {
        $result = $this->setCommand("sendMessage", 
            array('chat_id' => $chat_id, 'text' => $text));
	return $result;
    }
    public function getWebhook()	
    {
        $json = file_get_contents('php://input');
        return json_decode($json);
    }
}
