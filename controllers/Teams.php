<?php

namespace Modules\Ladder\Controllers;

use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ParticipantMapper;
use Modules\Ladder\Mappers\TeamMemberMapper;
use Modules\User\Mappers\User as UserMapper;

class Teams extends \Ilch\Controller\Frontend
{
    /** @var string[] */
    private $allowedLogoExtensions = ['png', 'jpg', 'jpeg', 'webp'];

    public function indexAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $participantMapper = new ParticipantMapper();

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuLadder'), ['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index'])
            ->add($this->getTranslator()->trans('myTeams'));

        $this->getView()->set('teams', $participantMapper->getByCaptain((int)$this->getUser()->getId()));
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $participantMapper = new ParticipantMapper();
        $memberMapper = new TeamMemberMapper();
        $matchMapper = new MatchMapper();

        $team = $participantMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuLadder'), ['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index'])
            ->add($this->getTranslator()->trans('teams'), ['action' => 'index'])
            ->add($team['team_name']);

        $members = $memberMapper->getByParticipantId($id);
        $isCaptain = $this->getUser() && (int)$this->getUser()->getId() === (int)$team['captain_user_id'];

        $this->getView()
            ->set('team', $team)
            ->set('members', $members)
            ->set('matches', $matchMapper->getByParticipantId($id))
            ->set('isCaptain', $isCaptain);
    }

    public function editAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $id = (int)$this->getRequest()->getParam('id');
        $participantMapper = new ParticipantMapper();
        $memberMapper = new TeamMemberMapper();
        $userMapper = new UserMapper();

        $team = $participantMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']);
        }

        if ((int)$team['captain_user_id'] !== (int)$this->getUser()->getId()) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $id]);
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('remove_member_id')) {
                $removeMemberId = (int)$this->getRequest()->getPost('remove_member_id');
                foreach ($memberMapper->getByParticipantId($id) as $member) {
                    if ((int)$member['id'] === $removeMemberId && $member['role'] !== 'captain') {
                        $memberMapper->remove($removeMemberId);
                        break;
                    }
                }

                $participantMapper->updatePlayersCount($id, $memberMapper->countPlayers($id));
                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
            }

            if ($this->getRequest()->getPost('username')) {
                $username = trim((string)$this->getRequest()->getPost('username'));
                $user = $userMapper->getUserByName($username);
                if ($username !== '') {
                    if ($user) {
                        if (!$memberMapper->isUserInParticipant($id, (int)$user->getId())) {
                            $memberMapper->add([
                                'participant_id' => $id,
                                'user_id' => (int)$user->getId(),
                                'nickname' => $user->getName(),
                                'role' => 'member',
                            ]);
                        }
                    } else {
                        $memberMapper->add([
                            'participant_id' => $id,
                            'user_id' => null,
                            'nickname' => $username,
                            'role' => 'member',
                        ]);
                    }
                }

                $participantMapper->updatePlayersCount($id, $memberMapper->countPlayers($id));
                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
            }

            $participantMapper->save([
                'team_name' => trim((string)$this->getRequest()->getPost('team_name')),
                'tag' => trim((string)$this->getRequest()->getPost('tag')),
                'logo' => $this->resolveUpdatedLogo($team),
            ], $id);

            $participantMapper->updatePlayersCount($id, $memberMapper->countPlayers($id));
            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuLadder'), ['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index'])
            ->add($this->getTranslator()->trans('teams'), ['action' => 'index'])
            ->add($team['team_name'], ['action' => 'view', 'id' => $id])
            ->add($this->getTranslator()->trans('editTeam'));

        $this->getView()
            ->set('team', $team)
            ->set('members', $memberMapper->getByParticipantId($id));
    }

    private function resolveUpdatedLogo(array $team): ?string
    {
        $uploadedLogo = $this->handleLogoUpload();
        if ($uploadedLogo !== null) {
            if (!empty($team['logo']) && file_exists($team['logo'])) {
                @unlink($team['logo']);
            }
            return $uploadedLogo;
        }

        return $team['logo'] ?? null;
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

        $baseDir = 'application/modules/ladder/storage/teams';
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
