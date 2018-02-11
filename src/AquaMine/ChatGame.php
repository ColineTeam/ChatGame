<?php
namespace AquaMine;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use AquaMine\CallbackTask;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class ChatGame extends PluginBase implements Listener{
    public $answer;
    public $worktime;


    public function onEnable() {
        (new \ColineServices\Updater($this, 112, $this->getFile()))->update();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "gameTask")), $this->_getConfig()['timer_broadcast']);

       @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        if(!file_exists($this->getDataFolder()."config.yml")){

            $this->saveResource('config.yml');
        }

    }

    public function gameTask(){
        $numbers = [];
        for ($i = 0; $i < 2; $i++) {
            $numbers[] = mt_rand(1, 685);
        }
        if(!is_float($numbers[0]/$numbers[1])){
            $mark = '/';
            $marksend = '÷';
        }else if($numbers[0] <= 30 && $numbers[1] <= 30){
            $mark = '*';
            $marksend = '×';
        } else if($numbers[0] >= $numbers[1]){
            $mark = '-';
            $marksend = '-';
        } else {
            $mark = '+';
            $marksend = '+';
        }

        $answer = $numbers[0].$mark.$numbers[1];
        eval("\$answer = $answer;");

        $instance = $numbers[0] . " " . $marksend . " " . $numbers[1] . " = ?";
        $this->answer = round($answer, 1);
        $this->worktime = time();
          //echo $instance.PHP_EOL;
        foreach ($this->getServer()->getOnlinePlayers() as $player){

                $player->sendMessage("§7(§aЧат-Игра§7) §eТот кто первый ответит получит ".$this->_getConfig()['money_min']." ".$this->_getConfig()['currency_name']."§e (чем быстрее тем больше!)");
                $player->sendMessage("§7(§aЧат-Игра§7) §6Пример§7: §c ".$instance);

        }


    }
    public function onChat(\pocketmine\event\player\PlayerChatEvent $event){
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if(is_numeric($message)){
                if(round($message, 1) == $this->answer){
                    $time = time() - $this->worktime;

                    if($time <= 15){
                        $prize = $this->_getConfig()['money_min'] .'+'. $time*3;
                         eval("\$prize = $prize;");
                        $player->sendMessage("§7(§aЧат-Игра§7) §e Поздравляем вы справились за ".$time." сек"." Ваш приз {$prize} ".$this->_getConfig()['currency_name']);
                   } else {
                        $prize = $this->_getConfig()['money_min'] .'-'. $time;
                        eval("\$prize = $prize;");
                        $player->sendMessage("§7(§aЧат-Игра§7) §e Спасибо за участие, ваш приз ".$prize." ".$this->_getConfig()['currency_name']." по скольку вы справились за ".$time." сек");
                    }
                    if($prize < 1) $prize = 10;
                    $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($player, $prize);

                    $this->worktime = NULL;
                    $this->answer = NULL;
                    $event->setCancelled();
                }

        }
    }


    public function getPlayer($player){
         if($player instanceof Player){
            $player = $player->getName();
        }

        $player = strtolower($player);
        return $player;
    }
    public function _getConfig(){

        return $this->getConfig()->getAll();
    }
}




/**
 * Чат игра для AquaMIne
 *
 * @date 27.05.2016
 * @author Alexey
 */
