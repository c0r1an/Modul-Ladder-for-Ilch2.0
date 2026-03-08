<?php

namespace Modules\Ladder\Controllers\Admin;

use Modules\Ladder\Libraries\Points;
use Modules\Ladder\Libraries\Status;
use Modules\Ladder\Mappers\LadderMapper;
use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ParticipantMapper;

class Ladders extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'menuAdminLadder',
                'active' => false,
                'icon' => 'fa-solid fa-ranking-star',
                'url' => $this->getLayout()->getUrl(['controller' => 'ladders', 'action' => 'index']),
                [
                    'name' => 'createLadder',
                    'active' => false,
                    'icon' => 'fa-solid fa-circle-plus',
                    'url' => $this->getLayout()->getUrl(['controller' => 'ladders', 'action' => 'treat'])
                ]
            ],
            [
                'name' => 'disputes',
                'active' => false,
                'icon' => 'fa-solid fa-gavel',
                'url' => $this->getLayout()->getUrl(['controller' => 'disputes', 'action' => 'index'])
            ],
        ];

        if ($this->getRequest()->getActionName() === 'treat') {
            $items[0][0]['active'] = true;
        } else {
            $items[0]['active'] = true;
        }

        $this->getLayout()->addMenu('menuAdminLadder', $items);
    }

    public function indexAction()
    {
        $mapper = new LadderMapper();

        $this->getLayout()->getAdminHmenu()->add($this->getTranslator()->trans('menuAdminLadder'));
        $this->getView()->set('ladders', $mapper->getAll([], ['id' => 'DESC']));
    }

    public function treatAction()
    {
        $mapper = new LadderMapper();
        $id = (int)$this->getRequest()->getParam('id');
        $entry = $id ? $mapper->getById($id) : null;

        if ($id && !$entry) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            $allowedMaxParticipants = [4, 8, 16, 32, 64, 128];
            $startAt = trim((string)$this->getRequest()->getPost('start_at'));
            if ($startAt !== '') {
                $startAt = str_replace('T', ' ', $startAt);
                if (strlen($startAt) === 16) {
                    $startAt .= ':00';
                }
            }

            $endAt = trim((string)$this->getRequest()->getPost('end_at'));
            if ($endAt !== '') {
                $endAt = str_replace('T', ' ', $endAt);
                if (strlen($endAt) === 16) {
                    $endAt .= ':00';
                }
            } else {
                $endAt = null;
            }

            $maxParticipants = (int)$this->getRequest()->getPost('max_participants');
            if (!in_array($maxParticipants, $allowedMaxParticipants, true)) {
                $maxParticipants = 16;
            }

            $status = (string)$this->getRequest()->getPost('status');
            if (!in_array($status, Status::ladderStatuses(), true)) {
                $status = Status::LADDER_DRAFT;
            }

            $payload = [
                'title' => trim((string)$this->getRequest()->getPost('title')),
                'slug' => trim((string)$this->getRequest()->getPost('slug')),
                'banner' => trim((string)$this->getRequest()->getPost('banner')),
                'game' => trim((string)$this->getRequest()->getPost('game')),
                'team_size' => max(1, (int)$this->getRequest()->getPost('team_size')),
                'max_participants' => $maxParticipants,
                'start_at' => $startAt ?: date('Y-m-d H:i:s'),
                'end_at' => $endAt,
                'rules' => (string)$this->getRequest()->getPost('rules'),
                'points_win' => (int)$this->getRequest()->getPost('points_win'),
                'points_draw' => (int)$this->getRequest()->getPost('points_draw'),
                'points_loss' => (int)$this->getRequest()->getPost('points_loss'),
                'status' => $status,
                'created_by' => $entry['created_by'] ?? (int)$this->getUser()->getId(),
            ];

            $savedId = $mapper->save($payload, $id ?: null);
            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'participants', 'id' => $savedId]);
        }

        $this->getView()->set('entry', $entry);
    }

    public function participantsAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();

        $ladder = $ladderMapper->getById($id);
        if (!$ladder) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('ladder_status')) {
                $newStatus = (string)$this->getRequest()->getPost('ladder_status');
                if (in_array($newStatus, Status::ladderStatuses(), true)) {
                    $ladderMapper->setStatus($id, $newStatus);
                }
            }

            if ($this->getRequest()->getPost('set_status_id') && $this->getRequest()->getPost('set_status')) {
                $setStatus = (string)$this->getRequest()->getPost('set_status');
                if (in_array($setStatus, [
                    Status::PARTICIPANT_PENDING,
                    Status::PARTICIPANT_ACCEPTED,
                    Status::PARTICIPANT_REJECTED,
                    Status::PARTICIPANT_CHECKED_IN
                ], true)) {
                    $participantMapper->updateStatus((int)$this->getRequest()->getPost('set_status_id'), $setStatus);
                }
            }

            if ($this->getRequest()->getPost('delete_participant_id')) {
                $participantMapper->delete((int)$this->getRequest()->getPost('delete_participant_id'));
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'participants', 'id' => $id]);
        }

        $this->getView()
            ->set('ladder', $ladder)
            ->set('participants', $participantMapper->getByLadderId($id));
    }

    public function matchesAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();
        $matchMapper = new MatchMapper();

        $ladder = $ladderMapper->getById($id);
        if (!$ladder) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('generate')) {
                $result = $this->generateRoundRobinMatches($id);
                if ($result === 'ok') {
                    $ladderMapper->setStatus($id, Status::LADDER_RUNNING);
                    (new Points())->recalculate($id);
                } elseif ($result === 'existing') {
                    $this->redirect()->withMessage('matchesAlreadyGenerated', 'danger')->to(['action' => 'matches', 'id' => $id]);
                } else {
                    $this->redirect()->withMessage('insufficientParticipants', 'danger')->to(['action' => 'matches', 'id' => $id]);
                }
            }

            if ($this->getRequest()->getPost('reset')) {
                $matchMapper->deleteByLadderId($id);
                (new Points())->recalculate($id);
            }

            if ($this->getRequest()->getPost('match_id')) {
                $matchId = (int)$this->getRequest()->getPost('match_id');
                $newStatus = trim((string)$this->getRequest()->getPost('status')) ?: Status::MATCH_READY;
                if (!in_array($newStatus, Status::matchStatuses(), true)) {
                    $newStatus = Status::MATCH_READY;
                }

                $scheduledAt = trim((string)$this->getRequest()->getPost('scheduled_at'));
                if ($scheduledAt !== '') {
                    $scheduledAt = str_replace('T', ' ', $scheduledAt);
                    if (strlen($scheduledAt) === 16) {
                        $scheduledAt .= ':00';
                    }
                } else {
                    $scheduledAt = null;
                }

                $payload = [
                    'map' => trim((string)$this->getRequest()->getPost('map')),
                    'best_of' => max(1, (int)$this->getRequest()->getPost('best_of')),
                    'scheduled_at' => $scheduledAt,
                    'status' => $newStatus,
                ];

                if ($newStatus === Status::MATCH_DONE) {
                    $score1Raw = trim((string)$this->getRequest()->getPost('score1'));
                    $score2Raw = trim((string)$this->getRequest()->getPost('score2'));
                    if ($score1Raw === '' || $score2Raw === '') {
                        $this->redirect()->withMessage('invalidState', 'danger')->withInput()->to(['action' => 'matches', 'id' => $id]);
                    }

                    $score1 = (int)$score1Raw;
                    $score2 = (int)$score2Raw;
                    $match = $matchMapper->getById($matchId);
                    $winnerId = null;
                    if ($score1 > $score2) {
                        $winnerId = $match ? (int)$match['participant1_id'] : null;
                    } elseif ($score2 > $score1) {
                        $winnerId = $match ? (int)$match['participant2_id'] : null;
                    }

                    $payload['score1'] = $score1;
                    $payload['score2'] = $score2;
                    $payload['winner_participant_id'] = $winnerId;
                }

                $matchMapper->update($matchId, $payload);
                (new Points())->recalculate($id);
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'matches', 'id' => $id]);
        }

        $this->getView()
            ->set('ladder', $ladder)
            ->set('matches', $matchMapper->getByLadderId($id))
            ->set('participantCount', $participantMapper->countAcceptedByLadder($id));
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id > 0) {
                $mapper = new LadderMapper();
                $mapper->delete($id);
                $this->addMessage('deleteSuccess');
            }
        }

        $this->redirect(['action' => 'index']);
    }

    private function generateRoundRobinMatches(int $ladderId): string
    {
        $participantMapper = new ParticipantMapper();
        $matchMapper = new MatchMapper();

        if ($matchMapper->countByLadderId($ladderId) > 0) {
            return 'existing';
        }

        $participants = $participantMapper->getAcceptedByLadderId($ladderId);
        if (count($participants) < 2) {
            return 'insufficient';
        }

        $ids = array_map(static function (array $row): int {
            return (int)$row['id'];
        }, $participants);

        if (count($ids) % 2 !== 0) {
            $ids[] = 0;
        }

        $n = count($ids);
        $rounds = $n - 1;
        $half = (int)($n / 2);
        $matchNo = 1;

        for ($round = 1; $round <= $rounds; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $a = (int)$ids[$i];
                $b = (int)$ids[$n - 1 - $i];
                if ($a === 0 || $b === 0) {
                    continue;
                }

                if ($round % 2 === 0) {
                    $tmp = $a;
                    $a = $b;
                    $b = $tmp;
                }

                $matchMapper->create([
                    'ladder_id' => $ladderId,
                    'round' => $round,
                    'match_no' => $matchNo++,
                    'participant1_id' => $a,
                    'participant2_id' => $b,
                    'winner_participant_id' => null,
                    'score1' => null,
                    'score2' => null,
                    'best_of' => 1,
                    'map' => null,
                    'scheduled_at' => null,
                    'status' => Status::MATCH_READY,
                ]);
            }

            $fixed = $ids[0];
            $rest = array_slice($ids, 1);
            $last = array_pop($rest);
            array_unshift($rest, $last);
            $ids = array_merge([$fixed], $rest);
        }

        return 'ok';
    }
}
