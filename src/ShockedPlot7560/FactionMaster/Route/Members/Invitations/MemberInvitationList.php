<?php

namespace ShockedPlot7560\FactionMaster\Route\Members\Invitations;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MemberInvitationList implements Route {

    const SLUG = "memberInvitationList";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->Invitations = MainAPI::getInvitationsBySender($Faction->name, "member");
        $this->buttons = [];
        foreach ($this->Invitations as $key => $Invitation) {
            $this->buttons[] = $Invitation->receiver;
        }
        $this->buttons[] = "§4Back";
        if (isset($params[0])) $message = $params[0];
        if (count($this->Invitations) == 0) $message .= "\n \n§4No pending invitations";
        $menu = $this->memberInvitationList($message);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return function (Player $player, $data) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageMemberInvitation::SLUG), $player, [$this->Invitations[$data]]);
            }
            return;
        };
    }

    private function memberInvitationList(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Invitations list");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }


}