<?php

namespace Modules\Ladder\Controllers;

use Modules\Ladder\Libraries\Status;
use Modules\Ladder\Mappers\LadderMapper;
use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ParticipantMapper;
use Modules\Ladder\Mappers\TeamMemberMapper;

class Ladders extends \Ilch\Controller\Frontend
{
    /** @var string[] */
    private $allowedLogoExtensions = ['png', 'jpg', 'jpeg', 'webp'];

    public function indexAction()
    {
        $mapper = new LadderMapper();
        $statusOptions = Status::ladderStatuses();
        $status = (string)$this->getRequest()->getParam('status');
        if (!in_array($status, $statusOptions, true)) {
            $status = '';
        }

        $where = [];
        if ($status !== '') {
            $where['status'] = $status;
        }

        $ladders = $mapper->getAll($where, ['start_at' => 'ASC']);
        if ($status === '') {
            $ladders = array_values(array_filter($ladders, static function (array $ladder): bool {
                return ($ladder['status'] ?? '') !== Status::LADDER_ARCHIVED;
            }));
        }

        $this->getLayout()->getHmenu()->add($this->getTranslator()->trans('menuLadder'));
        $this->getView()
            ->set('ladders', $ladders)
            ->set('selectedStatus', $status)
            ->set('statusOptions', $statusOptions);
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();
        $teamMemberMapper = new TeamMemberMapper();
        $matchMapper = new MatchMapper();

        $ladder = $ladderMapper->getById($id);
        if (!$ladder) {
            $this->redirect()->withMessage('noLadders', 'danger')->to(['action' => 'index']);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuLadder'), ['action' => 'index'])
            ->add($ladder['title']);

        $participants = $participantMapper->getByLadderId($id);
        $matches = $matchMapper->getByLadderId($id);
        $standings = $participantMapper->getStandingsByLadderId($id);

        $currentParticipant = null;
        if ($this->getUser()) {
            $currentParticipant = $participantMapper->getByLadderAndCaptain($id, (int)$this->getUser()->getId());
        }

        $isRegistrationOpen = ($ladder['status'] ?? '') === Status::LADDER_REGISTRATION_OPEN;
        $canJoin = $this->getUser() && $isRegistrationOpen && !$currentParticipant;
        $canLeave = $this->getUser()
            && $isRegistrationOpen
            && $currentParticipant
            && in_array($currentParticipant['status'], [Status::PARTICIPANT_PENDING, Status::PARTICIPANT_ACCEPTED], true);

        if ($canJoin && $participantMapper->countAcceptedByLadder($id) >= (int)$ladder['max_participants']) {
            $canJoin = false;
        }

        $this->getView()
            ->set('ladder', $ladder)
            ->set('participants', $participants)
            ->set('standings', $standings)
            ->set('matches', $matches)
            ->set('currentParticipant', $currentParticipant)
            ->set('canJoin', $canJoin)
            ->set('canLeave', $canLeave);
    }

    public function joinAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->withMessage('noRights', 'danger')->to([
                'module' => 'user',
                'controller' => 'login',
                'action' => 'index',
            ]);
        }

        $ladderId = (int)$this->getRequest()->getParam('id');
        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();

        $ladder = $ladderMapper->getById($ladderId);
        if (!$ladder) {
            $this->redirect()->withMessage('noLadders', 'danger')->to(['action' => 'index']);
        }

        if (($ladder['status'] ?? '') !== Status::LADDER_REGISTRATION_OPEN) {
            $this->redirect()->withMessage('registrationClosed', 'danger')->to(['action' => 'view', 'id' => $ladderId]);
        }

        $userId = (int)$this->getUser()->getId();
        if ($participantMapper->getByLadderAndCaptain($ladderId, $userId)) {
            $this->redirect()->to(['action' => 'view', 'id' => $ladderId]);
        }

        if ($participantMapper->countAcceptedByLadder($ladderId) >= (int)$ladder['max_participants']) {
            $this->redirect()->withMessage('registrationFull', 'danger')->to(['action' => 'view', 'id' => $ladderId]);
        }

        $teamName = trim((string)$this->getRequest()->getPost('team_name'));
        $playersCount = max(1, (int)$this->getRequest()->getPost('players_count'));
        if ($teamName === '') {
            $this->redirect()->withMessage('emptyMessage', 'danger')->withInput()->to(['action' => 'view', 'id' => $ladderId]);
        }

        if ($playersCount < (int)$ladder['team_size']) {
            $this->redirect()->withMessage('teamNotComplete', 'danger')->withInput()->to(['action' => 'view', 'id' => $ladderId]);
        }

        $participantId = $participantMapper->create([
            'ladder_id' => $ladderId,
            'team_name' => $teamName,
            'tag' => trim((string)$this->getRequest()->getPost('tag')),
            'logo' => $this->handleLogoUpload(),
            'captain_user_id' => $userId,
            'status' => Status::PARTICIPANT_ACCEPTED,
            'players_count' => $playersCount,
        ]);

        $teamMemberMapper->add([
            'participant_id' => $participantId,
            'user_id' => $userId,
            'nickname' => $this->getUser()->getName(),
            'role' => 'captain',
        ]);

        if ($playersCount > 1) {
            for ($i = 2; $i <= $playersCount; $i++) {
                $teamMemberMapper->add([
                    'participant_id' => $participantId,
                    'user_id' => null,
                    'nickname' => $teamName . ' Player ' . $i,
                    'role' => 'member',
                ]);
            }
        }

        $participantMapper->updatePlayersCount($participantId, $teamMemberMapper->countPlayers($participantId));

        $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $ladderId]);
    }

    public function leaveAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to([
                'module' => 'user',
                'controller' => 'login',
                'action' => 'index',
            ]);
        }

        $ladderId = (int)$this->getRequest()->getParam('id');
        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();

        $ladder = $ladderMapper->getById($ladderId);
        if (!$ladder) {
            $this->redirect()->withMessage('noLadders', 'danger')->to(['action' => 'index']);
        }

        if (($ladder['status'] ?? '') !== Status::LADDER_REGISTRATION_OPEN) {
            $this->redirect()->withMessage('registrationClosed', 'danger')->to(['action' => 'view', 'id' => $ladderId]);
        }

        $participant = $participantMapper->getByLadderAndCaptain($ladderId, (int)$this->getUser()->getId());
        if (!$participant) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $ladderId]);
        }

        if (!empty($participant['logo']) && file_exists($participant['logo'])) {
            @unlink($participant['logo']);
        }

        $participantMapper->delete((int)$participant['id']);
        $this->redirect()->withMessage('deleteSuccess')->to(['action' => 'view', 'id' => $ladderId]);
    }

    private function handleLogoUpload(): ?string
    {
        if (!isset($_FILES['logo']) || empty($_FILES['logo']['name']) || !is_uploaded_file($_FILES['logo']['tmp_name'])) {
            return null;
        }

        $extension = strtolower((string)pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedLogoExtensions, true)) {
            return null;
        }

        $imageInfo = @getimagesize($_FILES['logo']['tmp_name']);
        if ($imageInfo === false || strpos((string)$imageInfo['mime'], 'image/') !== 0) {
            return null;
        }

        $baseDir = 'application/modules/ladder/storage/participants';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $target = $baseDir . '/' . $filename;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
            return null;
        }

        return $target;
    }
}
