<?php
/** @var \Ilch\View $this */
$team = $this->get('team');
$members = $this->get('members') ?: [];
$matches = $this->get('matches') ?: [];
$isCaptain = (bool)$this->get('isCaptain');
$playerCount = is_array($members) ? count($members) : 0;
$teamViewCss = 'application/modules/ladder/static/css/team-view.css';
$teamViewCssVersion = @filemtime(dirname(__DIR__, 2) . '/static/css/team-view.css') ?: time();
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl($teamViewCss . '?v=' . $teamViewCssVersion) ?>">

<div class="tk-team-view">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
        <?php if ($this->getUser()): ?>
            <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
        <?php endif; ?>
    </div>

    <div class="card tk-card rounded-0 mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold"><?=$this->escape($team['team_name']) ?></span>
            <?php if ($isCaptain): ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['action' => 'edit', 'id' => $team['id']]) ?>"><?=$this->getTrans('editTeam') ?></a>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="tk-team-logo">
                    <?php if (!empty($team['logo'])): ?>
                        <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->getTrans('teamLogoAlt') ?>">
                    <?php else: ?>
                        <span><?=strtoupper(substr((string)$team['team_name'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="h3 mb-2"><?=$this->escape($team['team_name']) ?></h1>
                    <span class="badge text-bg-secondary rounded-0"><?=$this->getTrans('tag') ?>: <?=$this->escape($team['tag'] ?: '-') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('tag') ?></div>
                    <div class="fw-semibold"><?=$this->escape($team['tag'] ?: '-') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('playersCount') ?></div>
                    <div class="fw-semibold"><?=$playerCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('status') ?></div>
                    <div class="fw-semibold"><?=$this->getTrans($team['status']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header">
            <h4 class="h6 mb-0"><?=$this->getTrans('members') ?></h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($members as $member): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a class="tk-member-link" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'members', 'action' => 'view', 'id' => (int)$member['id']]) ?>">
                            <div class="card tk-card rounded-0 h-100">
                                <div class="card-body">
                                    <div class="fw-semibold mb-1"><?=$this->escape($member['nickname'] ?: ('User#' . $member['user_id'])) ?></div>
                                    <div class="small text-secondary text-uppercase"><?=$this->escape($member['role']) ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card tk-card rounded-0">
        <div class="card-header">
            <h4 class="h6 mb-0"><?=$this->getTrans('matches') ?></h4>
        </div>
        <div class="card-body">
            <?php if ($matches): ?>
                <div class="row g-3">
                    <?php foreach ($matches as $match): ?>
                        <div class="col-12 col-lg-6">
                            <div class="card rounded-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?=$this->getTrans('round') ?> <?=$this->escape((string)$match['round']) ?></strong>
                                            <span class="text-muted">#<?=$this->escape((string)$match['match_no']) ?></span>
                                        </div>
                                        <span class="badge text-bg-secondary"><?=$this->getTrans($match['status']) ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <strong><?=$this->escape($match['participant1_tag'] ?: $match['participant1_name'] ?: $this->getTrans('tbd')) ?></strong>
                                        <span class="mx-2"><?=$this->getTrans('vs') ?></span>
                                        <strong><?=$this->escape($match['participant2_tag'] ?: $match['participant2_name'] ?: $this->getTrans('tbd')) ?></strong>
                                    </div>
                                    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'matches', 'action' => 'view', 'id' => (int)$match['id']]) ?>"><?=$this->getTrans('matchView') ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mb-0">-</p>
            <?php endif; ?>
        </div>
    </div>
</div>
