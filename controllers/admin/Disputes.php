<?php

namespace Modules\Ladder\Controllers\Admin;

use Modules\Ladder\Libraries\Points;
use Modules\Ladder\Libraries\Status;
use Modules\Ladder\Mappers\AuditMapper;
use Modules\Ladder\Mappers\DisputeMapper;
use Modules\Ladder\Mappers\EvidenceMapper;
use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ReportMapper;

class Disputes extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'menuAdminLadder',
                'active' => false,
                'icon' => 'fa-solid fa-ranking-star',
                'url' => $this->getLayout()->getUrl(['controller' => 'ladders', 'action' => 'index']),
            ],
            [
                'name' => 'disputes',
                'active' => true,
                'icon' => 'fa-solid fa-gavel',
                'url' => $this->getLayout()->getUrl(['controller' => 'disputes', 'action' => 'index']),
            ],
        ];

        $this->getLayout()->addMenu('menuAdminLadder', $items);
    }

    public function indexAction()
    {
        $mapper = new DisputeMapper();
        $allowedStatuses = [Status::DISPUTE_OPEN, Status::DISPUTE_RESOLVED, Status::DISPUTE_REJECTED];
        $status = (string)$this->getRequest()->getParam('status');
        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $this->getView()
            ->set('disputes', $mapper->getAll($status))
            ->set('selectedStatus', $status)
            ->set('statusOptions', $allowedStatuses);
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $disputeMapper = new DisputeMapper();
        $matchMapper = new MatchMapper();
        $reportMapper = new ReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $auditMapper = new AuditMapper();

        $allowedStatuses = [Status::DISPUTE_OPEN, Status::DISPUTE_RESOLVED, Status::DISPUTE_REJECTED];
        $dispute = $disputeMapper->getById($id);
        if (!$dispute) {
            $this->redirect()->to(['action' => 'index']);
        }

        $match = $matchMapper->getById((int)$dispute['match_id']);
        if (!$match) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            $newStatus = (string)$this->getRequest()->getPost('dispute_status');
            if (!in_array($newStatus, $allowedStatuses, true)) {
                $newStatus = Status::DISPUTE_OPEN;
            }

            $note = trim((string)$this->getRequest()->getPost('resolution_note'));
            $userId = (int)$this->getUser()->getId();

            if ($newStatus === Status::DISPUTE_RESOLVED) {
                $score1Raw = trim((string)$this->getRequest()->getPost('score1'));
                $score2Raw = trim((string)$this->getRequest()->getPost('score2'));
                $score1 = $score1Raw === '' ? -1 : (int)$score1Raw;
                $score2 = $score2Raw === '' ? -1 : (int)$score2Raw;
                if ($score1 < 0 || $score2 < 0) {
                    $this->redirect()->withMessage('invalidState', 'danger')->withInput()->to(['action' => 'view', 'id' => $id]);
                }

                $winnerId = null;
                if ($score1 > $score2) {
                    $winnerId = (int)$match['participant1_id'];
                } elseif ($score2 > $score1) {
                    $winnerId = (int)$match['participant2_id'];
                }

                $matchMapper->update((int)$match['id'], [
                    'score1' => $score1,
                    'score2' => $score2,
                    'winner_participant_id' => $winnerId,
                    'status' => Status::MATCH_DONE,
                ]);

                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_RESOLVED,
                    'resolved_by_user_id' => $userId,
                    'resolution_note' => $note,
                    'resolved_at' => date('Y-m-d H:i:s'),
                ]);

                (new Points())->recalculate((int)$match['ladder_id']);
                $auditMapper->log('match', (int)$match['id'], 'dispute_resolved', [
                    'dispute_id' => $id,
                    'score1' => $score1,
                    'score2' => $score2,
                    'winner_participant_id' => $winnerId,
                    'note' => $note,
                ], $userId);
            } elseif ($newStatus === Status::DISPUTE_REJECTED) {
                $latestReport = $reportMapper->getLatestByMatchId((int)$match['id']);
                if ($latestReport) {
                    $matchMapper->update((int)$match['id'], [
                        'score1' => (int)$latestReport['score1'],
                        'score2' => (int)$latestReport['score2'],
                        'winner_participant_id' => !empty($latestReport['winner_participant_id']) ? (int)$latestReport['winner_participant_id'] : null,
                        'status' => Status::MATCH_DONE,
                    ]);
                }

                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_REJECTED,
                    'resolved_by_user_id' => $userId,
                    'resolution_note' => $note,
                    'resolved_at' => date('Y-m-d H:i:s'),
                ]);

                (new Points())->recalculate((int)$match['ladder_id']);
                $auditMapper->log('match', (int)$match['id'], 'dispute_rejected', [
                    'dispute_id' => $id,
                    'note' => $note,
                ], $userId);
            } else {
                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_OPEN,
                    'resolved_by_user_id' => null,
                    'resolution_note' => $note,
                    'resolved_at' => null,
                ]);

                if (($match['status'] ?? '') !== Status::MATCH_DONE) {
                    $matchMapper->update((int)$match['id'], ['status' => Status::MATCH_DISPUTE]);
                }
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $id]);
        }

        $reports = $reportMapper->getByMatchId((int)$match['id']);
        foreach ($reports as &$report) {
            $report['evidence'] = $evidenceMapper->getByReportId((int)$report['id']);
        }

        $this->getView()
            ->set('dispute', $dispute)
            ->set('match', $match)
            ->set('reports', $reports)
            ->set('statusOptions', $allowedStatuses);
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id > 0) {
                $mapper = new DisputeMapper();
                $mapper->delete($id);
                $this->addMessage('deleteSuccess');
            }
        }

        $this->redirect(['action' => 'index']);
    }
}
