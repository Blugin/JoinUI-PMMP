<?php
/**
 * @name JoinUI
 * @author alvin0319
 * @main JoinUI\JoinUI
 * @version 1.0.0
 * @api 4.0.0
 */
namespace JoinUI;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\{
ModalFormRequestPacket, ModalFormResponsePacket
};
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\{
Command, PluginCommand, CommandSender
};
use pocketmine\utils\Config;
use pocketmine\Player;
//한글깨짐방지
class JoinUI extends PluginBase implements Listener{
	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, [
		"UI 메시지" => "§d§l[ §f서버§d ] §f안녕하세요, (이름)님.(줄바꿈)저희 서버에 오신걸 환영합니다!(줄바꿈)저희 서버는 JoinUI 시스템을 이용한 UI 알림창 서비스를 제공하고 있습니다.(줄바꿈)즐거운 하루 되세요~~"
		]);
		$this->db = $this->config->getAll();
		$cmd = new PluginCommand("joinui", $this);
		$cmd->setDescription("JoinUI");
		$this->getServer()->getCommandMap()->register("joinui", $cmd);
	}
	public function sendUI(Player $player, $code, $data) {
		$pk = new ModalFormRequestPacket();
		$pk->formId = $code;
		$pk->formData = $data;
		$player->dataPacket($pk);
	}
	public function onJoin(PlayerJoinEvent $event) {
		$name = $event->getPlayer()->getName();
		$info = str_replace(["(이름)", "(줄바꿈)"] , [$name, "\n"], $this->db["UI 메시지"]);
		$this->sendUI($event->getPlayer(), 2, $this->MainData($info));
	}
	public function MainData($info) {
		$encode = [
		"type" => "form",
		"title" => "UIJoin",
		"content" => "{$info}",
		"buttons" => [
		[
		"text" => "확인"
		]
		]
		];
		return json_encode($encode);
	}
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if ($cmd->getName() === "joinui") {
			if (! isset($args[0])) {
				$sender->sendMessage("/joinui [접속시 띄울 ui 메시지...]\n(줄바꿈) 으로 줄을 바꿀수 있으며 (이름) 으로 플레이어의 이름을 보여줄수 있습니다");
				return true;
			}
			$a = implode(" ", $args);
			$this->db["UI 메시지"] = $a;
			$sender->sendMessage("등록되었습니다: {$a}");
			$this->save();
		}
		return true;
	}
	public function save() {
		$this->config->setAll($this->db);
		$this->config->save();
	}
}