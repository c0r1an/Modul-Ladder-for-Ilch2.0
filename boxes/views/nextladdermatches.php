<?php
/** @var \Ilch\View $this */
$matches = $this->get('matches');
?>
<link href="<?=$this->getBoxUrl('static/css/box.css') ?>" rel="stylesheet">
<h5 class="mb-2"><?=$this->getTrans('nextMatches') ?></h5>

<?php if ($matches): ?>
    <div class="tk-module-box-list">
        <?php foreach ($matches as $match): ?>
            <a class="tk-box-link" href="<?=$this->getUrl('ladder/matches/view/id/' . (int)$match['id']) ?>">
                <div class="card tk-module-card rounded-0">
                    <div class="card-body">
                        <div class="tk-box-headline">
                            <?=!empty($match['scheduled_at']) ? $this->escape(date('d.m.Y H:i', strtotime($match['scheduled_at']))) : '-' ?>
                        </div>

                        <div class="tk-nextmatch-grid">
                            <div class="tk-team tk-team-left">
                                <div class="tk-team-logo">
                                    <?php if (!empty($match['participant1_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['participant1_logo']) ?>" alt="<?=$this->escape($match['participant1_tag'] ?: $this->getTrans('teamOne')) ?>">
                                    <?php else: ?>
                                        <span class="tk-logo-fallback"><?=$this->escape($match['participant1_tag'] ?: 'P1') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="tk-team-tag"><?=$this->escape($match['participant1_tag'] ?: $this->getTrans('tbd')) ?></div>
                            </div>

                            <div class="tk-nextmatch-vs"><?=$this->getTrans('vs') ?></div>

                            <div class="tk-team tk-team-right">
                                <div class="tk-team-tag"><?=$this->escape($match['participant2_tag'] ?: $this->getTrans('tbd')) ?></div>
                                <div class="tk-team-logo">
                                    <?php if (!empty($match['participant2_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['participant2_logo']) ?>" alt="<?=$this->escape($match['participant2_tag'] ?: $this->getTrans('teamTwo')) ?>">
                                    <?php else: ?>
                                        <span class="tk-logo-fallback"><?=$this->escape($match['participant2_tag'] ?: 'P2') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="tk-nextmatch-meta">
                            <div><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($match['ladder_game'] ?: '-') ?></div>
                            <div><strong><?=$this->getTrans('ladder') ?>:</strong> <?=$this->escape($match['ladder_title'] ?: '-') ?></div>
                            <div><strong><?=$this->getTrans('round') ?>:</strong> <?=$this->escape((string)$match['round']) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="mb-0"><?=$this->getTrans('noUpcomingMatches') ?></p>
<?php endif; ?>
