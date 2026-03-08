<?php
/** @var \Ilch\View $this */
$ladder = $this->get('ladder');
$participants = $this->get('participants') ?: [];
$standings = $this->get('standings') ?: [];
$matches = $this->get('matches') ?: [];
$currentParticipant = $this->get('currentParticipant');
$canJoin = (bool)$this->get('canJoin');
$canLeave = (bool)$this->get('canLeave');
$isRegistrationOpen = ($ladder['status'] ?? '') === 'registration_open';
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl('application/modules/ladder/static/css/ladder.css') ?>">

<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
    <?php if ($this->getUser()): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
    <?php endif; ?>
</div>

<h1><?=$this->escape($ladder['title']) ?></h1>
<?php if (!empty($ladder['banner'])): ?>
    <p><img src="<?=$this->getBaseUrl($ladder['banner']) ?>" alt="<?=$this->escape($ladder['title']) ?>" style="width: 100%; max-height: 320px; object-fit: cover;"></p>
<?php endif; ?>

<div class="row">
    <div class="col-xl-8">
        <div class="card rounded-0 mb-3">
            <div class="card-header"><?=$this->getTrans('overview') ?></div>
            <div class="card-body">
                <p><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($ladder['game']) ?></p>
                <p><strong><?=$this->getTrans('teamSize') ?>:</strong> <?=$this->escape($ladder['team_size']) ?></p>
                <p><strong><?=$this->getTrans('maxParticipants') ?>:</strong> <?=$this->escape($ladder['max_participants']) ?></p>
                <p><strong><?=$this->getTrans('pointSystem') ?>:</strong> <?=$this->escape($ladder['points_win']) ?> / <?=$this->escape($ladder['points_draw']) ?> / <?=$this->escape($ladder['points_loss']) ?> (W/D/L)</p>
                <p><strong><?=$this->getTrans('startAt') ?>:</strong> <?=!empty($ladder['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($ladder['start_at']))) : '-' ?></p>
                <p><strong><?=$this->getTrans('status') ?>:</strong> <span class="badge text-bg-secondary"><?=$this->getTrans($ladder['status']) ?></span></p>
                <div><?=$ladder['rules'] ?></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <?php if ($this->getUser()): ?>
            <div class="card rounded-0 mb-3">
                <div class="card-header"><?=$this->getTrans('register') ?></div>
                <div class="card-body">
                    <?php if ($canJoin): ?>
                        <form method="POST" action="<?=$this->getUrl(['action' => 'join', 'id' => $ladder['id']]) ?>" enctype="multipart/form-data">
                            <?=$this->getTokenField() ?>
                            <div class="mb-2">
                                <label class="form-label" for="team_name"><?=$this->getTrans('teamName') ?></label>
                                <input class="form-control" type="text" id="team_name" name="team_name" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="tag"><?=$this->getTrans('tag') ?></label>
                                <input class="form-control" type="text" id="tag" name="tag" maxlength="32">
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="players_count"><?=$this->getTrans('playersCount') ?></label>
                                <input class="form-control" type="number" min="1" id="players_count" name="players_count" value="<?=$this->escape((string)$ladder['team_size']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="logo"><?=$this->getTrans('logo') ?></label>
                                <input class="form-control" type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp">
                            </div>
                            <button class="btn btn-primary" type="submit"><?=$this->getTrans('register') ?></button>
                        </form>
                    <?php elseif ($canLeave && !empty($currentParticipant)): ?>
                        <p class="mb-3"><?=$this->getTrans('alreadyRegisteredAs') ?> <strong><?=$this->escape($currentParticipant['team_name']) ?></strong>.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$currentParticipant['id']]) ?>"><?=$this->getTrans('teamView') ?></a>
                            <form method="POST" action="<?=$this->getUrl(['action' => 'leave', 'id' => $ladder['id']]) ?>">
                                <?=$this->getTokenField() ?>
                                <button class="btn btn-outline-danger btn-sm" type="submit"><?=$this->getTrans('leaveLadder') ?></button>
                            </form>
                        </div>
                    <?php elseif ($currentParticipant): ?>
                        <p class="mb-0"><?=$this->getTrans('alreadyRegisteredAs') ?> <strong><?=$this->escape($currentParticipant['team_name']) ?></strong>.</p>
                        <div class="mt-3">
                            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$currentParticipant['id']]) ?>"><?=$this->getTrans('teamView') ?></a>
                        </div>
                    <?php elseif (!$isRegistrationOpen): ?>
                        <p class="mb-0"><?=$this->getTrans('registrationClosedInfo') ?></p>
                    <?php else: ?>
                        <p class="mb-0"><?=$this->getTrans('registrationFull') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card rounded-0 mb-3">
    <div class="card-header"><?=$this->getTrans('standings') ?></div>
    <div class="card-body p-0">
        <?php if ($standings): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?=$this->getTrans('team') ?></th>
                            <th><?=$this->getTrans('playedShort') ?></th>
                            <th><?=$this->getTrans('winsShort') ?></th>
                            <th><?=$this->getTrans('drawsShort') ?></th>
                            <th><?=$this->getTrans('lossesShort') ?></th>
                            <th><?=$this->getTrans('scoreShort') ?></th>
                            <th><?=$this->getTrans('points') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($standings as $idx => $row): ?>
                        <tr>
                            <td><?=$idx + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($row['logo'])): ?>
                                        <img class="tk-mini-logo" src="<?=$this->getBaseUrl($row['logo']) ?>" alt="<?=$this->escape($row['team_name']) ?>">
                                    <?php endif; ?>
                                    <a href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$row['id']]) ?>"><?=$this->escape($row['team_name']) ?></a>
                                    <?php if (!empty($row['tag'])): ?>
                                        <small class="text-muted">(<?=$this->escape($row['tag']) ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?=$this->escape($row['played']) ?></td>
                            <td><?=$this->escape($row['wins']) ?></td>
                            <td><?=$this->escape($row['draws']) ?></td>
                            <td><?=$this->escape($row['losses']) ?></td>
                            <td><?=$this->escape($row['score_for']) ?>:<?=$this->escape($row['score_against']) ?></td>
                            <td><strong><?=$this->escape($row['points']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="p-3 mb-0">-</p>
        <?php endif; ?>
    </div>
</div>

<div class="card rounded-0 mb-3">
    <div class="card-header"><?=$this->getTrans('participants') ?></div>
    <div class="card-body">
        <?php if ($participants): ?>
            <div class="row g-3">
                <?php foreach ($participants as $participant): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <a class="text-decoration-none text-reset d-block h-100" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$participant['id']]) ?>">
                        <div class="card rounded-0 h-100">
                            <div class="card-body text-center">
                                <div class="tk-participant-logo-wrapper mb-2">
                                    <?php if (!empty($participant['logo'])): ?>
                                        <img class="tk-participant-logo" src="<?=$this->getBaseUrl($participant['logo']) ?>" alt="<?=$this->escape($participant['team_name']) ?>">
                                    <?php else: ?>
                                        <span class="tk-participant-logo tk-participant-logo-fallback"><?=$this->escape(strtoupper(substr($participant['team_name'], 0, 1))) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="mb-1"><?=$this->escape($participant['team_name']) ?></h5>
                                <p class="text-muted mb-1"><?=$this->getTrans('tag') ?>: <?=$this->escape($participant['tag'] ?: '-') ?></p>
                                <p class="text-muted mb-1"><?=$this->getTrans('playersCount') ?>: <?=$this->escape((string)$participant['players_count']) ?></p>
                                <span class="badge text-bg-secondary"><?=$this->getTrans($participant['status']) ?></span>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mb-0">-</p>
        <?php endif; ?>
    </div>
</div>

<div class="card rounded-0 mb-3">
    <div class="card-header"><?=$this->getTrans('matches') ?></div>
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
                                <p class="mb-2 text-muted"><?=!empty($match['scheduled_at']) ? $this->escape(date('d.m.Y H:i', strtotime($match['scheduled_at']))) : '-' ?></p>
                                <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'matches', 'action' => 'view', 'id' => $match['id']]) ?>"><?=$this->getTrans('matchView') ?></a>
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
