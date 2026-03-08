<?php

namespace Modules\Ladder\Controllers;

use Modules\Ladder\Libraries\EvidenceUploader;
use Modules\Ladder\Libraries\Points;
use Modules\Ladder\Libraries\Status;
use Modules\Ladder\Mappers\AuditMapper;
use Modules\Ladder\Mappers\DisputeMapper;
use Modules\Ladder\Mappers\EvidenceMapper;
use Modules\Ladder\Mappers\LadderMapper;
use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ParticipantMapper;
use Modules\Ladder\Mappers\ReportMapper;

class Matches extends \Ilch\Controller\Frontend
{
    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $matchMapper = new MatchMapper();
        $reportMapper = new ReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $disputeMapper = new DisputeMapper();
        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();

        $match = $matchMapper->getById($id);
        if (!$match) {
            $this->redirect()->to(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']);
        }

        $ladder = $ladderMapper->getById((int)$match['ladder_id']);

        $hmenu = $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuLadder'), ['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']);
        if ($ladder) {
            $hmenu->add($ladder['title'], ['module' => 'ladder', 'controller' => 'ladders', 'action' => 'view', 'id' => (int)$ladder['id']]);
        }
        $hmenu->add($this->getTranslator()->trans('matchNumber', (string)$id));

        $reports = $reportMapper->getByMatchId($id);
        foreach ($reports as &$report) {
            $report['evidence'] = $evidenceMapper->getByReportId((int)$report['id']);
        }

        $user = $this->getUser();
        $userId = $user ? (int)$user->getId() : 0;
        $hasReportPermission = $user && ($user->isAdmin() || $user->hasAccess('module_ladder') || $user->hasAccess('ladder_report'));
        $hasDisputePermission = $user && ($user->isAdmin() || $user->hasAccess('module_ladder') || $user->hasAccess('ladder_report') || $user->hasAccess('ladder_dispute'));

        $isCaptain1 = !empty($match['participant1_id']) && $participantMapper->isCaptain((int)$match['participant1_id'], $userId);
        $isCaptain2 = !empty($match['participant2_id']) && $participantMapper->isCaptain((int)$match['participant2_id'], $userId);

        $canReport = $hasReportPermission
            && ($isCaptain1 || $isCaptain2)
            && in_array($match['status'], [Status::MATCH_READY, Status::MATCH_SCHEDULED], true);

        $canOpenDispute = $hasDisputePermission
            && ($isCaptain1 || $isCaptain2)
            && $match['status'] === Status::MATCH_REPORTED;

        $canConfirm = $hasReportPermission
            && ($isCaptain1 || $isCaptain2)
            && $match['status'] === Status::MATCH_REPORTED;

        $this->getView()
            ->set('match', $match)
            ->set('reports', $reports)
            ->set('disputes', $disputeMapper->getByMatchId($id))
            ->set('canReport', $canReport)
            ->set('canOpenDispute', $canOpenDispute)
            ->set('canConfirm', $canConfirm);
    }

    public function reportAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');

        $matchMapper = new MatchMapper();
        $reportMapper = new ReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $participantMapper = new ParticipantMapper();
        $auditMapper = new AuditMapper();
        $uploader = new EvidenceUploader();

        $match = $matchMapper->getById($matchId);
        if (!$match) {
            $this->redirect()->to(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']);
        }

        if (!in_array($match['status'], [Status::MATCH_READY, Status::MATCH_SCHEDULED], true)) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$this->getUser()->getId();
        $isCaptain1 = !empty($match['participant1_id']) && $participantMapper->isCaptain((int)$match['participant1_id'], $userId);
        $isCaptain2 = !empty($match['participant2_id']) && $participantMapper->isCaptain((int)$match['participant2_id'], $userId);
        if (!$isCaptain1 && !$isCaptain2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $score1 = (int)$this->getRequest()->getPost('score1');
        $score2 = (int)$this->getRequest()->getPost('score2');
        if ($score1 < 0 || $score2 < 0) {
            $this->redirect()->withMessage('invalidState', 'danger')->withInput()->to(['action' => 'view', 'id' => $matchId]);
        }

        $winnerId = null;
        if ($score1 > $score2) {
            $winnerId = (int)$match['participant1_id'];
        } elseif ($score2 > $score1) {
            $winnerId = (int)$match['participant2_id'];
        }

        $reportedBy = $isCaptain1 ? (int)$match['participant1_id'] : (int)$match['participant2_id'];
        $reportId = $reportMapper->create([
            'match_id' => $matchId,
            'reported_by_participant_id' => $reportedBy,
            'score1' => $score1,
            'score2' => $score2,
            'winner_participant_id' => $winnerId,
            'comment' => trim((string)$this->getRequest()->getPost('comment')),
        ]);

        $uploadedFiles = $uploader->upload($_FILES['evidence_files'] ?? [], (int)$match['ladder_id'], $matchId);
        foreach ($uploadedFiles as $path) {
            $evidenceMapper->add([
                'match_report_id' => $reportId,
                'type' => 'screenshot',
                'path_or_url' => $path,
                'note' => null,
            ]);
        }

        $linkLines = preg_split('/\r\n|\r|\n/', (string)$this->getRequest()->getPost('evidence_links'));
        foreach ($linkLines as $link) {
            $link = trim((string)$link);
            if ($link !== '') {
                $evidenceMapper->add([
                    'match_report_id' => $reportId,
                    'type' => 'link',
                    'path_or_url' => $link,
                    'note' => null,
                ]);
            }
        }

        $matchMapper->update($matchId, ['status' => Status::MATCH_REPORTED]);
        $auditMapper->log('match', $matchId, 'reported', [
            'score1' => $score1,
            'score2' => $score2,
            'winner_participant_id' => $winnerId,
            'report_id' => $reportId,
        ], $userId);

        $this->redirect()->withMessage('resultSubmitted')->to(['action' => 'view', 'id' => $matchId]);
    }

    public function confirmAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');
        $matchMapper = new MatchMapper();
        $reportMapper = new ReportMapper();
        $participantMapper = new ParticipantMapper();
        $auditMapper = new AuditMapper();

        $match = $matchMapper->getById($matchId);
        $report = $reportMapper->getLatestByMatchId($matchId);

        if (!$match || !$report || $match['status'] !== Status::MATCH_REPORTED) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$this->getUser()->getId();
        $isCaptain1 = !empty($match['participant1_id']) && $participantMapper->isCaptain((int)$match['participant1_id'], $userId);
        $isCaptain2 = !empty($match['participant2_id']) && $participantMapper->isCaptain((int)$match['participant2_id'], $userId);

        if (!$isCaptain1 && !$isCaptain2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        if ((int)$report['reported_by_participant_id'] === (int)$match['participant1_id'] && !$isCaptain2) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }
        if ((int)$report['reported_by_participant_id'] === (int)$match['participant2_id'] && !$isCaptain1) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $matchMapper->update($matchId, [
            'score1' => (int)$report['score1'],
            'score2' => (int)$report['score2'],
            'winner_participant_id' => !empty($report['winner_participant_id']) ? (int)$report['winner_participant_id'] : null,
            'status' => Status::MATCH_DONE,
        ]);

        (new Points())->recalculate((int)$match['ladder_id']);
        $auditMapper->log('match', $matchId, 'confirmed', [
            'score1' => (int)$report['score1'],
            'score2' => (int)$report['score2'],
            'winner_participant_id' => !empty($report['winner_participant_id']) ? (int)$report['winner_participant_id'] : null,
            'report_id' => (int)$report['id'],
        ], $userId);

        $this->redirect()->withMessage('resultConfirmed')->to(['action' => 'view', 'id' => $matchId]);
    }

    public function disputeAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');
        $matchMapper = new MatchMapper();
        $reportMapper = new ReportMapper();
        $participantMapper = new ParticipantMapper();
        $disputeMapper = new DisputeMapper();

        $match = $matchMapper->getById($matchId);
        $report = $reportMapper->getLatestByMatchId($matchId);
        if (!$match || !$report || $match['status'] !== Status::MATCH_REPORTED) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $user = $this->getUser();
        $hasDisputePermission = $user->isAdmin()
            || $user->hasAccess('module_ladder')
            || $user->hasAccess('ladder_report')
            || $user->hasAccess('ladder_dispute');
        if (!$hasDisputePermission) {
            $this->redirect()->withMessage('noRights', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$user->getId();
        $isCaptain1 = !empty($match['participant1_id']) && $participantMapper->isCaptain((int)$match['participant1_id'], $userId);
        $isCaptain2 = !empty($match['participant2_id']) && $participantMapper->isCaptain((int)$match['participant2_id'], $userId);
        if (!$isCaptain1 && !$isCaptain2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $reason = trim((string)$this->getRequest()->getPost('reason'));
        if ($reason === '') {
            $this->redirect()->withMessage('emptyMessage', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $openedBy = $isCaptain1 ? (int)$match['participant1_id'] : (int)$match['participant2_id'];
        $disputeMapper->create([
            'match_id' => $matchId,
            'opened_by_participant_id' => $openedBy,
            'reason' => $reason,
            'status' => Status::DISPUTE_OPEN,
        ]);

        $matchMapper->update($matchId, ['status' => Status::MATCH_DISPUTE]);
        $this->redirect()->withMessage('disputeCreated')->to(['action' => 'view', 'id' => $matchId]);
    }
}
