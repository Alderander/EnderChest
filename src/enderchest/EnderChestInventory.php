<?php
namespace enderchest;

use pocketmine\Player;
use pocketmine\inventory\FakeBlockMenu;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\BlockEventPacket;

use pocketmine\inventory\ContainerInventory;

/*
 * Developed by TheAz928 (Az928)
 * CopyRight (C) @TheAz928, All
 * Rights (R) reserved. This software
 * Is distributed under GNU General
 * Public License v3.0.0 and later
 * You can modify the code by giving
 * The original author (TheAz928) Credits
 * And you cannot take credits yourself
 */

class EnderChestInventory extends ContainerInventory{
	
	/** @var owner */
	private $owner = null;
	
	/** @var holder */
	protected $holder = null;
	
	public function __construct(Player $owner){
	    $this->owner = $owner;
		 parent::__construct(new FakeBlockMenu($this, $owner->getPosition()));
	    $this->init();
	}
	
	/**
	 * @return FakeBlockMenu
	 */
	
	public function getHolder(){
	    return $this->holder;
	}
	
	/**
	 * @return Player
	 */
	
	public function getOwner(): Player{
	    return $this->owner;
	}
	
	/**
	 * @return Int
	 */
	
	public function getNetworkType(): Int{
	    return 0;
	}
	
	/**
	 * @return Int
	 */
	
	public function getDefaultSize(): Int{
	    return 27;
	}
		
	/**
	 * @return string
	 */
	
	public function getName(): string{
	    return "Ender Chest";
	}
	
	/**
	 * @void updateHolderPosition
	 * @param Position $pos
	 */
	
	public function updateHolderPosition(Position $pos): void{
	    $this->getHolder()->setComponents($pos->x, $pos->y, $pos->z);
	    $this->getHolder()->setLevel($pos->getLevel());
	}
	
	/**
	 * @void init
	 */
	
	public function init(): void{
	    $items = DataBase::getInventoryContents($this->getOwner());
	    if(is_array($items)){
		   $this->setContents($items);
		}
	}
	
	/**
	 * @void onClose
	 */
	
	public function onClose(Player $player): void{
		 parent::onClose($player);
	    DataBase::saveInventoryContents($player, $this);
	    if(count($this->getViewers()) <= 1){
			$this->broadcastBlockEventPacket($this->getHolder(), false);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), 66);
		}
	}
	
	/**
	 * @void onOpen
	 */

	public function onOpen(Player $player): void{
		parent::onOpen($player);
		if(count($this->getViewers()) == 1){
			$this->broadcastBlockEventPacket($this->getHolder(), true);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), 65);
		}
	}
	
	/**
	 * @void broadcastBlockEventPacket
	 */

	public function broadcastBlockEventPacket(Vector3 $vector, bool $setOpen): void{
		$pk = new BlockEventPacket();
		$pk->x = (Int) $vector->x;
		$pk->y = (Int) $vector->y;
		$pk->z = (Int) $vector->z;
		$pk->eventType = 1;
		$pk->eventData = $setOpen ? 1: 0;
		$this->getHolder()->getLevel()->addChunkPacket($this->getHolder()->getX() >> 4, $this->getHolder()->getZ() >> 4, $pk);
	}
}