<?php
/** @var \Ilch\View $this */
$match = $this->get('match');
$reports = $this->get('reports') ?: [];
$disputes = $this->get('disputes') ?: [];
$canReport = (bool)$this->get('canReport');
$canOpenDispute = (bool)$this->get('canOpenDispute');
$canConfirm = (bool)$this->get('canConfirm');

$p1Name = $match['participant1_name'] ?: $this->getTrans('tbd');
$p2Name = $match['participant2_name'] ?: $this->getTrans('tbd');
$p1Tag = $match['participant1_tag'] ?: '-';
$p2Tag = $match['participant2_tag'] ?: '-';
$p1Initial = strtoupper(substr($p1Name, 0, 1));
$p2Initial = strtoupper(substr($p2Name, 0, 1));
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl('application/modules/ladder/static/css/match-view.css') ?>">

<div class="tk-match-view">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
        <?php if ($this->getUser()): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
        <?php endif; ?>
    </div>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->escape($this->getTrans('matchNumber', (string)$match['id'])) ?></div>
        <div class="card-body">
            <div class="row align-items-center g-3 mb-3">
                <div class="col-md-5">
                    <a class="tk-team-box" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$match['participant1_id']]) ?>">
                        <div class="tk-team-logo">
                            <?php if (!empty($match['participant1_logo'])): ?>
                                <img src="<?=$this->getBaseUrl($match['participant1_logo']) ?>" alt="<?=$this->escape($p1Name) ?>">
                            <?php else: ?>
                                <span><?=$this->escape($p1Initial) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="tk-team-text">
                            <div class="tk-team-name"><?=$this->escape($p1Name) ?></div>
                            <div class="tk-team-tag"><?=$this->getTrans('tag') ?>: <?=$this->escape($p1Tag) ?></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-2 text-center">
                    <span class="tk-vs-badge"><?=$this->getTrans('vs') ?></span>
                </div>

                <div class="col-md-5">
                    <a class="tk-team-box" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$match['participant2_id']]) ?>">
                        <div class="tk-team-logo">
                            <?php if (!empty($match['participant2_logo'])): ?>
                                <img src="<?=$this->getBaseUrl($match['participant2_logo']) ?>" alt="<?=$this->escape($p2Name) ?>">
                            <?php else: ?>
                                <span><?=$this->escape($p2Initial) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="tk-team-text">
                            <div class="tk-team-name"><?=$this->escape($p2Name) ?></div>
                            <div class="tk-team-tag"><?=$this->getTrans('tag') ?>: <?=$this->escape($p2Tag) ?></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('round') ?></div>
                        <div class="tk-meta-value"><?=$this->escape((string)$match['round']) ?> / <?=$this->escape((string)$match['match_no']) ?></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('status') ?></div>
                        <div class="tk-meta-value"><span class="badge text-bg-secondary"><?=$this->getTrans($match['status']) ?></span></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('map') ?></div>
                        <div class="tk-meta-value"><?=$this->escape($match['map'] ?: '-') ?></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('bestOf') ?></div>
                        <div class="tk-meta-value"><?=$this->escape((string)$match['best_of']) ?></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('scheduledAt') ?></div>
                        <div class="tk-meta-value"><?=!empty($match['scheduled_at']) ? $this->escape(date('d.m.Y H:i', strtotime($match['scheduled_at']))) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($canReport): ?>
    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('reportResult') ?></div>
        <div class="card-body">
            <form method="POST" action="<?=$this->getUrl(['action' => 'report', 'id' => $match['id']]) ?>" enctype="multipart/form-data">
                <?=$this->getTokenField() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="score1"><?=$this->getTrans('scoreTeam1') ?></label>
                        <input class="form-control" type="number" min="0" name="score1" id="score1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="score2"><?=$this->getTrans('scoreTeam2') ?></label>
                        <input class="form-control" type="number" min="0" name="score2" id="score2" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="comment"><?=$this->getTrans('comment') ?></label>
                    <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="evidence_files"><?=$this->getTrans('evidenceUpload') ?></label>
                    <input class="form-control" type="file" name="evidence_files[]" id="evidence_files" multiple accept=".png,.jpg,.jpeg,.webp,.pdf">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="evidence_links"><?=$this->getTrans('evidenceLinks') ?></label>
                    <textarea class="form-control" name="evidence_links" id="evidence_links" rows="2" placeholder="https://..."></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><?=$this->getTrans('reportResult') ?></button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($match['status'] === 'reported' && ($canConfirm || $canOpenDispute)): ?>
    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('disputes') ?></div>
        <div class="card-body">
            <?php if ($canConfirm): ?>
                <form class="d-inline-block me-2 mb-3" method="POST" action="<?=$this->getUrl(['action' => 'confirm', 'id' => $match['id']]) ?>">
                    <?=$this->getTokenField() ?>
                    <button class="btn btn-success" type="submit"><?=$this->getTrans('confirmResult') ?></button>
                </form>
            <?php endif; ?>
            <?php if ($canOpenDispute): ?>
                <form method="POST" action="<?=$this->getUrl(['action' => 'dispute', 'id' => $match['id']]) ?>">
                    <?=$this->getTokenField() ?>
                    <div class="mb-3">
                        <label class="form-label" for="reason"><?=$this->getTrans('reason') ?></label>
                        <textarea class="form-control" name="reason" id="reason" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger" type="submit"><?=$this->getTrans('openDispute') ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('reports') ?></div>
        <div class="card-body">
            <?php if ($reports): ?>
                <?php foreach ($reports as $report): ?>
                    <div class="card tk-sub-card rounded-0 mb-2">
                        <div class="card-body">
                            <p class="mb-1"><strong><?=$this->getTrans('score') ?>:</strong> <?=$this->escape((string)$report['score1']) ?> : <?=$this->escape((string)$report['score2']) ?></p>
                            <p class="mb-2"><strong><?=$this->getTrans('comment') ?>:</strong> <?=$this->escape($report['comment'] ?: '-') ?></p>
                            <?php if (!empty($report['evidence'])): ?>
                                <p class="mb-1"><strong><?=$this->getTrans('evidence') ?>:</strong></p>
                                <ul class="mb-0">
                                    <?php foreach ($report['evidence'] as $evidence): ?>
                                        <li>
                                            <?php if ($evidence['type'] === 'link'): ?>
                                                <a href="<?=$this->escape($evidence['path_or_url']) ?>" target="_blank" rel="noopener noreferrer"><?=$this->escape($evidence['path_or_url']) ?></a>
                                            <?php else: ?>
                                                <a href="<?=$this->getBaseUrl($evidence['path_or_url']) ?>" target="_blank" rel="noopener noreferrer"><?=$this->escape($evidence['path_or_url']) ?></a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">-</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('disputes') ?></div>
        <div class="card-body">
            <?php if ($disputes): ?>
                <?php foreach ($disputes as $dispute): ?>
                    <div class="card tk-sub-card rounded-0 mb-2">
                        <div class="card-body">
                            <div><strong>#<?=$this->escape((string)$dispute['id']) ?></strong></div>
                            <div><strong><?=$this->getTrans('status') ?>:</strong> <?=$this->getTrans($dispute['status']) ?></div>
                            <div><strong><?=$this->getTrans('reason') ?>:</strong> <?=$this->escape($dispute['reason']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">-</p>
            <?php endif; ?>
        </div>
    </div>
</div>
