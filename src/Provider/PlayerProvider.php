<?php

namespace App\Provider;

use App\Entity\Game;
use App\Entity\Player;
use App\PlayerManipulator;
use App\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PlayerProvider
{
    /**
     * Used to get the player from the session
     * 
     * @var SessionInterface $session
     */
    private $session;

    /**
     * Used to get the player from the storage
     * 
     * @var PlayerRepository $playerRepository
     */
    private $playerRepository;

    /**
     * Used to update player hash
     * 
     * @var PlayerManipulator $playerManipulator
     */
    private $playerManipulator;

    /**
     * Inject dependencies
     * 
     * @param SessionInterface $session
     * @param PlayerRepository $playerRepository
     * @param PlayerManipulator $playerManipulator
     */
    public function __construct(SessionInterface $session, PlayerRepository $playerRepository, PlayerManipulator $playerManipulator)
    {
        $this->session = $session;
        $this->playerRepository = $playerRepository;
        $this->playerManipulator = $playerManipulator;
    }

    /**
     * Fetch the player from th storage corresponding to the session hash
     * 
     * @param Game $game
     * @return Player
     */
    public function getPlayer(Game $game): ?Player
    {
        $playerHash = $this->session->get('player_hash');

        // For demo purpose, we consider that only the second player comes
        // TODO: better management of the security
        if ($playerHash) {
            $player = $this->playerRepository->findOneBy(['hash' => $playerHash]);
        } else {
            $player = $this->playerManipulator->generatePlayerHash($game->getPlayer2());
            $this->session->set('player_hash', $player->getHash());
        }

        return $player;
    }

    /**
     * Put in session the first player hash
     * 
     * @param Game $game
     */
    public function initFirstPlayerSession(Game $game) 
    {
        $this->session->set('player_hash', $game->getPlayer1()->getHash());
    }
}
