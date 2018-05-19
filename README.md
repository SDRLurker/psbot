# 텔레그램 숫자야구 봇

텔레그램 API를 사용하여 숫자야구 봇을 만들었습니다.

숫자야구 봇 매뉴얼 설명 : http://psbbbot.herokuapp.com/psbot/

## 파일목록

### Telegram.php
### TelegramException.php

텔레그램 API를 처리하기 위해 만든 Telegram class가 구현되어 있습니다.

https://github.com/akalongman/php-telegram-bot

위의 소스를 참고하여 작성하였습니다.

### db.php

데이터베이스를 처리하기 위한 변수들을 정의합니다.

### ps.sql

숫자야구 봇을 구동하기 위해 필요한 table SQL 파일입니다.

### register.php

텔레그램 봇의 webhook 주소를 설정하기 위해 필요한 파일입니다.

https만 가능하며 https://주소/register.php?url=https://주소/hook.php 로 webhook주소를 등록합니다.

다음처럼 결과가 출력되면 webhook 주소 등록이 성공한 것입니다.

Array ( [status] => 0 [result] => {"ok":true,"result":true,"description":"Webhook was set"} )

## hook.php
webhook주소가 등록되어 있으면 사용자가 텔레그램 봇에게 메세지를 보낼 때 텔레그램 서버가 그 내용에 대해 POST방식으로 JSON형식의 메세지 정보를 보내게 됩니다.

메세지 정보를 받아 Baseball.php를 이용하여 숫자야구 게임을 진행하게 됩니다.

## Baseball.php
텔레그램 서버로부터 받은 JSON 형식의 메세지 정보를 이용하여 숫자야구 게임 진행에 필요한 로직을 처리합니다.

## lang.json
숫자야구 봇이 보낼 메세지를 영어와 한글로 저장해 놓은 JSON 파일입니다.
