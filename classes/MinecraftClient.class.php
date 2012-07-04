<?php
require_once(dirname(__FILE__)."/Utils.class.php");
require_once(dirname(__FILE__)."/Packet.class.php");
require_once(dirname(__FILE__)."/Socket.class.php");

require_once(dirname(__FILE__)."/Entity.class.php");

require_once(dirname(__FILE__)."/../functions.php");


class MinecraftClient{
	private $server, $port, $protocol, $auth, $player, $entities;
	protected $spout, $events, $cnt, $responses, $info, $inventory, $time, $timeState, $stop, $connected, $actions;
	
	
	function __construct($server, $protocol = CURRENT_PROTOCOL, $port = "25565"){
		$this->server = $server;
		$this->port = $port;

		$this->protocol = intval($protocol);
		$this->interface = new MinecraftInterface($server, $protocol, $port);
		$this->cnt = 1;
		$this->events = array("recieved_ff" => array(0 => array('close', true)));
		$this->responses = array();
		$this->info = array();
		$this->entities = array();
		$this->inventory = array();
		$this->connected = true;
		$this->actions = array();
		$this->spout = false;
	}
	
	public function activateSpout(){
		$this->spout = true;
		$this->interface->pstruct["c3"] = array(
			"short",
			"short",
			"int",
			"byteArray",	
		);
	}
	
	public function logout($message = "Quitting"){
		$this->send("ff", array(0 => $message));
		sleep(1);
		$this->close(array("data" => array(0 => $message)));
	}
	
	protected function close($data = ""){
		
		if($data !== ""){
			$this->trigger("onClose", $data[0]);
			console("[-] Kicked from server, ".$data[0]);
		}else{
			$this->trigger("onClose");
		}
		$this->interface->close();
		$this->connected = false;
		$this->stop();
	}
	
	public function getInventory(){
		return $this->inventory;
	}
	
	public function getInventorySlot($id){
		return $this->inventory[$id];
	}
	
	public function changeSlot($id){
		$this->send("10", array(0 => $id));	
	}
	
	protected function send($pid, $data = array(), $raw = false){
		if($this->connected === true){
			$this->trigger("sent_".$pid, $data);
			$this->trigger("onSentPacket", array("pid" => $pid, "data" => $data));
			$this->interface->writePacket($pid, $data, $raw);
		}
	}
	
	public function stop(){
		$this->stop = true;
	}	
	
	public function process($stop = "ff"){
		$pid = "";
		$this->stop = false;
		while($pid != $stop and $this->stop === false and $this->connected === true){
			$packet = $this->interface->readPacket();
			$this->trigger("onRecievedPacket", $packet);
			$pid = $packet["pid"];
			$this->trigger("recieved_".$pid, $packet["data"]);
		}
	}
	
	public function trigger($event, $data = ""){
		console("[*] Event ". $event, true, true, 2);
		if(isset($this->events[$event])){
			foreach($this->events[$event] as $eid => $ev){
				if(isset($ev[1]) and $ev[1] == true){
					$this->responses[$eid] = call_user_func(array($this, $ev[0]), $data, $event, $this);
				}else{
					$this->responses[$eid] = call_user_func($ev[0], $data, $event, $this);
				}
			}
		}	
	}	
	
	public function response($eid){
		if(isset($this->responses[$eid])){
			$res = $this->responses[$eid];
			unset($this->responses[$eid]);
			return $res;
		}
		return false;
	}

	public function action($microseconds, $code){
		$this->actions[] = array($microseconds, 0, $code);
	}
	
	public function event($event, $func, $in = false){
		++$this->cnt;
		if(!isset($this->events[$event])){
			$this->events[$event] = array();
		}
		$this->events[$event][$this->cnt] = array($func, $in);
		return $this->cnt;
	}
	
	public function deleteEvent($event, $id = -1){
		if($id === -1){
			unset($this->events[$event]);
		}else{
			unset($this->events[$event][$id]);
			if(count($this->events[$event]) == 0){
				unset($this->events[$event]);
			}
		}
	}
	
	public function ping($data = ""){
		if($data === ""){
			$this->send("fe");
			$eid = $this->event("recieved_ff", 'ping', true);
			$this->process();
			return $this->response($eid);
		}else{
			return explode("\xa7", $data[0]);
			$this->close();
		}
	}
	
	public function say($message){
		$this->trigger("onChatSent", $message);
		$this->send("03", array(
			0 => $message,
		));
	}
	
	public function move($x, $y, $z, $ground = true){
		$this->player->setCoords($x, $y, $z);
		$this->player->setGround($ground);
		$this->send("0b",$this->player->packet("0b"));
		$this->trigger("onMove", $this->player);
		$this->trigger("onEntityMove", $this->player);
	}
	
	public function useEntity($eid, $left = true){
		$this->trigger("onUseEntity", array("eid" => $eid, "left" => $left));
		$this->send("07", array(
			0 => $this->player->getEID(),
			1 => $eid,
			2 => $left,
		));		
	}
	
	public function dropSlot(){
		$this->trigger("onDropSlot");
		$this->send("0e", array(
			0 => 4,
			1 => 0,
			2 => 0,
			3 => 0,
		));		
	}
	
	public function tickerFunction(){
		//actions that repeat every x time will go here
		$time = explode(" ",microtime());
		$time = $time[1] + floatval($time[0]);
		foreach($this->actions as $id => $action){
			if($action[1] <= ($time - ($action[0] / 1000000))){
				$this->actions[$id][1] = $time;
				eval($action[2]);
			}
		}	
	}
	
	private function backgroundHandler($data, $event){
		switch($event){
			case "onRecievedPacket":
				$this->tickerFunction();
				break;
			case "onPluginMessage_REGISTER":
				$this->trigger("onPluginChannelRegister", $data);
				$this->trigger("onPluginChannelRegister_".$data);
				break;
			case "onPluginMessage_UNREGISTER":
				$this->trigger("onPluginChannelUnregister", $data);
				$this->trigger("onPluginChannelUnegister_".$data);
				break;
		}	
	}
	
	private function handler($data, $event){
		$pid = str_replace("recieved_", "", $event);
		switch($pid){
			case "00":
				$this->send("00", array(0 => $data[0]));
				break;
			case "03":
				console("[*] Chat: ".$data[0]);
				$this->trigger("onChat", $data[0]);
				break;
			case "04":
				$this->time = $data[0] % 24000;
				console("[*] Time: ".((intval($this->time/1000+6) % 24)).':'.str_pad(intval(($this->time/1000-floor($this->time/1000))*60),2,"0",STR_PAD_LEFT).', '.(($this->time > 23100 or $this->time < 12900) ? "day":"night")."   \r", false, false);
				$this->trigger("onTimeChange", $this->time);
				$timeState = (($this->time > 23100 or $this->time < 12900) ? "day":"night");
				if($this->timeState != $timeState){
					$this->timeState = $timeState;
					$this->trigger("onTimeStateChange", $this->timeState);
					if($this->timeState == "day"){
						$this->trigger("onDay");
					}else{
						$this->trigger("onNight");
					}
				}
				break;
			case "06":
				$this->info["spawn"] = array("x" => $data[0], "y" => $data[1], "z" => $data[2]);
				console("[+] Got spawn: (".$data[0].",".$data[1].",".$data[2].")");
				$this->trigger("onSpawnChange", $this->info["spawn"]);
				break;
			case "08":
				$this->player->setHealth($data[0]);
				if(isset($data[1])){ //Food
					$this->player->setFood($data[1]);
					console("[*] Health: ".$data[0].", Food: ". $data[1]);
				}else{
					console("[*] Health: ".$data[0]);
				}
				$this->trigger("onHealthChange", array("health" => $this->player->getHealth(), "food" => $this->player->getFood()));
				if($data[0] <= 0){ //Respawn
					$d = array(					
						0 => $this->info["dimension"],
						1 => 1,
						2 => $this->info["mode"],
						3 => $this->info["height"],
						4 => $this->info["seed"],
					);
					if($this->protocol >= 23){
						$d[5] = $this->info["level_type"];
					}					
					$this->send("09", $d);
					$this->trigger("onRespawn", $d);
					console("[-] Death and respawn");
				}
				break;
			case "0d":
				$this->player->setPosition($data[0], $data[2], $data[3], $data[1], $data[4], $data[5], $data[6]);
				$this->send("0d",$this->player->packet("0d"));
				console("[+] Got position: (".$data[0].",".$data[2].",".$data[3].")");
				$this->trigger("onMove", $this->player);
				$this->trigger("onEntityMove", $this->player);
				break;
			case "14":
				$this->entities[$data[0]] = new Entity($data[0], 0);
				$this->entities[$data[0]]->setName($data[1]);
				$this->entities[$data[0]]->setCoords($data[2] / 32,$data[3] / 32,$data[4] / 32);
				console("[+] Player \"".$data[1]."\" (EID: ".$data[0].") spawned at (".($data[2] / 32).",".($data[3] / 32).",".($data[4] / 32).")");
				$this->trigger("onPlayerSpawn", $this->entities[$data[0]]);
				$this->trigger("onEntitySpawn", $this->entities[$data[0]]);
				break;
			case "15":
				console("[+] Item (EID: ".$data[0].") type ".$data[1]." spawned at (".($data[4] / 32).",".($data[5] / 32).",".($data[6] / 32).")");
				$this->entities[$data[0]] = new Entity($data[0], $data[1], true);
				$this->entities[$data[0]]->setCoords($data[4] / 32,$data[5] / 32,$data[6] / 32);
				$this->trigger("onEntitySpawn", $this->entities[$data[0]]);				
				break;
			case "17":
			case "18":
				console("[+] Entity (EID: ".$data[0].") type ".$data[1]." spawned at (".($data[2] / 32).",".($data[3] / 32).",".($data[4] / 32).")");
				$this->entities[$data[0]] = new Entity($data[0], $data[1], ($pid === "17" ? true:false));
				$this->entities[$data[0]]->setCoords($data[2] / 32,$data[3] / 32,$data[4] / 32);
				$this->trigger("onEntitySpawn", $this->entities[$data[0]]);
				break;
			case "1d":
				console("[*] EID ".$data[0]." despawned");
				$this->trigger("onEntityDespawn", $data[0]);
				unset($this->entities[$data[0]]);
				break;
			case "1f":
			case "21":
				if(isset($this->entities[$data[0]])){
					$this->entities[$data[0]]->move($data[1] / 32,$data[2] / 32,$data[3] / 32);
					$this->trigger("onEntityMove", $this->entities[$data[0]]);
				}
				break;
			case "22":
				if(isset($this->entities[$data[0]])){
					$this->entities[$data[0]]->setCoords($data[1] / 32,$data[2] / 32,$data[3] / 32);
					$this->trigger("onEntityMove", $this->entities[$data[0]]);
				}
				break;
			case "46";
				switch($data[0]){
					case 0:
						$m = "Invalid bed";
						break;
					case 1:
						$m = "Started raining";
						$this->trigger("onRainStart", $this->time);
						break;
					case 2:
						$m = "Ended raining";
						$this->trigger("onRainStop", $this->time);
						break;
					case 3:
						$m = "Gamemode changed: ".($data[1]==0 ? "survival":"creative");
						$this->trigger("onGamemodeChange", $data[1]);
						break;
					case 4:
						$m = "Entered credits";
						break;
				}
				console("[*] ".$m);
				break;
			case "47":
				console("[*] Thunderbolt at (".($data[2] / 32).",".($data[3] / 32).",".($data[4] / 32).")");
				$this->trigger("onThunderbolt", array("eid" => $data[0], "coords" => array("x" => $data[2] / 32, "y" => $data[3] / 32, "z" => $data[4] / 32)));				
				break;
			case "67":
				if($data[0] == 0){
					if(!isset($data[2][0])){
						$this->inventory[$data[1]] = array(0,0,0);
					}else{
						$this->inventory[$data[1]] = $data[2][0];
					}
					$this->trigger("onInventorySlotChanged", array("slot" => $data[1], "data" => $this->getInventorySlot($data[1])));
					$this->trigger("onInventoryChanged", $this->getInventory());
					console("[*] Changed inventory slot ".$data[1]);
				}
				break;				
			case "68":
				if($data[0] == 0){
					foreach($data[2] as $i => $slot){
						$this->inventory[$i] = $slot;
					}
					$this->trigger("onInventoryChanged", $this->getInventory());
					console("[+] Recieved inventory");
				}
				break;
			case "fa":
				$this->trigger("onPluginMessage", array("channel" => $data[0], "data" => $data[2]));
				$this->trigger("onPluginMessage_".$data[0], $data[2]);
				break;
		}
	}


	public function registerPluginChannel($channel){
		if($this->protocol < 23){
			return false;
		}
		$this->send("fa", array(
			0 => "REGISTER",
			1 => strlen($channel),
			2 => $channel,			
		));
	}

	public function unregisterPluginChannel($channel){
		if($this->protocol < 23){
			return false;
		}
		$this->send("fa", array(
			0 => "UNREGISTER",
			1 => strlen($channel),
			2 => $channel,			
		));
	}
	
	
	public function sendPluginMessage($channel, $data){
		if($this->protocol < 23){
			return false;
		}
		$this->send("fa", array(
			0 => $channel,
			1 => strlen($data),
			2 => $data,			
		));
	}
	
	protected function startHandlers(){
	
		if(ACTION_MODE === 1){
			declare(ticks=15);
			register_tick_function(array($this, "tickerFunction"));		
		}else{
			$this->event("onRecievedPacket", "backgroundHandler", true);
		}
		
		$this->event("recieved_00", "handler", true);
		$this->event("recieved_03", "handler", true);
		$this->event("recieved_04", "handler", true);
		$this->event("recieved_06", "handler", true);
		$this->event("recieved_08", "handler", true);
		$this->event("recieved_0d", "handler", true);
		$this->event("recieved_14", "handler", true);
		$this->event("recieved_15", "handler", true);
		$this->event("recieved_17", "handler", true);
		$this->event("recieved_18", "handler", true);
		$this->event("recieved_1d", "handler", true);
		$this->event("recieved_1f", "handler", true);
		$this->event("recieved_21", "handler", true);
		$this->event("recieved_22", "handler", true);
		$this->event("recieved_46", "handler", true);
		$this->event("recieved_47", "handler", true);
		$this->event("recieved_67", "handler", true);
		$this->event("recieved_68", "handler", true);
		$this->event("recieved_fa", "handler", true);
		$this->event("onPluginMessage_REGISTER", "backgroundHandler", true);
		$this->event("onPluginMessage_UNREGISTER", "backgroundHandler", true);
		
		$this->action(50000, '$this->player->setGround(true); $this->send("0d",$this->player->packet("0d"));');	
		
	}
	
	protected function authentication($data, $event){
		$pid = str_replace("recieved_", "", $event);
		switch($pid){
			case "02":
				$hash = $data[0];
				if($hash != "-" and $hash != "+"){
					console("[*] Server is Premium (SID: ".$hash.")");
					if($hash == "" or strpos($hash, "&") !== false){
						console("[!] NAME SPOOF DETECTED");
					}
					$secure = true;
					if($secure !== false){
						$proto = "https";
						console("[+] Using secure HTTPS connection");
					}else{
						$proto = "http";
					}
						
					$response = Utils::curl_get($proto."://login.minecraft.net/?user=".$this->auth["user"]."&password=".$this->auth["password"]."&version=12");
					switch($response){
						case 'Bad login':
						case 'Bad Login':
							console("[-] Bad login");
							$this->close();
							break;
						case "Old Version":
							console("[-] Old Version");
							$this->close();
							break;
						default:
							$content = explode(":",$response);
							if(!is_array($content)){
								console("[-] Unknown Login Error: \"".$response."\"");
								$this->close();
							}
							console("[+] Logged into minecraft.net");
							break;
					}
					$res = Utils::curl_get("http://session.minecraft.net/game/joinserver.jsp?user=".$this->auth["user"]."&sessionId=".$content[3]."&serverId=".$hash); //User check
					if($res != "OK"){
						console("[-] Error in User Check: \"".$res."\"");
						$this->close();
					}
				}
				$this->send("01", array(
					0 => $this->protocol,
					1 => $this->auth["user"],
				));
				$this->event("recieved_01", 'authentication', true);
				break;
			case "01":
				$this->info["seed"] = $data[2];
				$this->info["level_type"] = $this->protocol >= 23 ? $data[3]:0;
				$this->info["mode"] = $this->protocol <= 23 ? $data[4]:$data[3];
				$this->info["dimension"] = $this->protocol <= 23 ? $data[5]:$data[4];
				$this->info["difficulty"] = $this->protocol <= 23 ? $data[6]:$data[5];
				$this->info["height"] = $this->protocol <= 23 ? $data[7]:$data[6];
				$this->info["max_players"] = $this->protocol <= 23 ? $data[8]:$data[7];
				$this->entities[$data[0]] = new Entity($data[0], 0);
				$this->player =& $this->entities[$data[0]];				
				$this->player->setName($this->auth["user"]);
				console("[+] EID: ".$this->player->getEID());
				$this->startHandlers();
				$this->trigger("onConnect");
				$this->process();
				break;		
		}
	}
	
	public function connect($user, $password = ""){
		$this->auth = array("user" => $user, "password" => $password);
		if($this->spout == true){
			/*$this->registerPluginChannel("AutoProto:HShake");
			$this->registerPluginChannel("ChkCache:setHash");
			$this->sendPluginMessage("AutoProto:HShake", "VanillaProtocol");
			$this->event("onPluginChannelRegister_WECUI", "spoutHandler", true);*/
			$this->event("onConnect", "spoutHandler", true);
			$this->event("recieved_c3", "spoutHandler", true);
		}
		$this->send("02", array(
			0 => $user.($this->protocol >= 28 ? ";".$this->server.":".$this->port:""),
		));
		$this->event("recieved_02", 'authentication', true);
		$this->process("0d");
	}
	public function sendSpoutMessage($pid, $version, $data){
		$this->send("c3", array(
			0 => $pid,
			1 => $version,
			2 => strlen($data),
			3 => $data,
		));	
	}
	public function spoutHandler($data, $event){
		switch($event){
			case "recieved_c3":
				$packetId = $data[0];
				$version = $data[1];
				$packet = $data[3];
				console("[*] Recieved Spout packet ".$packetId, true, true, 2);
				$this->trigger("onSpoutPacket_".$packetId, array("version" => $version, "data" => $packet));
				$this->trigger("onRecievedSpoutPacket", array("id" => $packetId, "version" => $version, "data" => $packet));
				break;
			case "onPluginChannelRegister_WECUI":
				
				break;
			case "onConnect":
				$this->send("12", array(
					0 => -42,
					1 => 1,				
				));
				$this->sendSpoutMessage(5,0,"\x02\xff\xff");
				$this->sendSpoutMessage(33,0,"\x00\x04".Utils::writeString("1000"));
				$this->sendSpoutMessage(56,0,"\x00\x00");
				break;		
		}
	
	}	

}

class MinecraftInterface{
	private $server;
	protected $protocol;
	var $pstruct;
	
	function __construct($server, $protocol = CURRENT_PROTOCOL, $port = "25565"){
		$this->server = new Socket($server, $port);
		$this->protocol = intval($protocol);
		include(dirname(__FILE__)."/../pstruct/".$this->protocol.".php");
		$this->pstruct = $pstruct;
	}
	
	public function close(){
		return $this->server->close();
	}
	
	protected function getPID($chr){
		return Utils::strToHex($chr{0});
	}
	
	protected function getStruct($pid){
		if(isset($this->pstruct[$pid])){
			return $this->pstruct[$pid];
		}
		return false;
	}
	
	public function readPacket(){		
		$pid = $this->getPID($this->server->read(1));
		$struct = $this->getStruct($pid);
		if($struct === false){
			$this->server->unblock();
			$p = "==".time()."==> ERROR Bad packet id $pid :".PHP_EOL;
			$p .= hexdump(Utils::hexToStr($pid).$this->server->read(512), false, false, true);
			$p .= PHP_EOL . "--------------- (512 byte extract) ----------" .PHP_EOL .PHP_EOL;
			logg($p, "packets");
			
			$this->buffer = "";
			$this->server->recieve("\xff".Utils::writeString('Kicked from server, "Bad packet id '.$pid.'"'));
			$this->writePacket("ff", array(0 => Utils::writeString('Kicked from server, "Bad packet id '.$pid.'"')));
			return array("pid" => "ff", "data" => array(0 => 'Kicked from server, "Bad packet id '.$pid.'"'));
		}
		
		$packet = new Packet($pid, $struct, $this->server);
		$packet->parse();
		
		$len = strlen($packet->raw);
		$p = "==".time()."==> RECIEVED Packet $pid, lenght $len:".PHP_EOL;
		$p .= hexdump($packet->raw, false, false, true);
		$p .= PHP_EOL .PHP_EOL;
		logg($p, "packets", false);
		
		return array("pid" => $pid, "data" => $packet->data);
	}
	
	public function writePacket($pid, $data = array(), $raw = false){
		$struct = $this->getStruct($pid);
		$packet = new Packet($pid, $struct);
		$packet->data = $data;
		$packet->create($raw);
		$this->server->write($packet->raw);
		
		$len = strlen($packet->raw);
		$p = "==".time()."==> SENT Packet $pid, lenght $len:".PHP_EOL;
		$p .= hexdump($packet->raw, false, false, true);
		$p .= PHP_EOL .PHP_EOL;
		logg($p, "packets", false);		
		return true;
	}
	
}
?>