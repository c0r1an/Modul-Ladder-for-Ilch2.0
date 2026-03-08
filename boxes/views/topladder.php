<?php
/** @var \Ilch\View $this */
$entries = $this->get('entries');
?>
<link href="<?=$this->getBoxUrl('static/css/box.css') ?>" rel="stylesheet">
<h5 class="mb-2"><?=$this->getTrans('topLadder') ?></h5>

<?php if ($entries): ?>
    <div class="tk-module-box-list">
        <?php foreach ($entries as $idx => $entry): ?>
            <a class="tk-box-link" href="<?=$this->getUrl('ladder/ladders/view/id/' . (int)$entry['ladder_id']) ?>">
                <div class="card tk-module-card rounded-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="tk-team-tag">#<?=$idx + 1 ?> <?=$this->escape($entry['tag'] ?: $entry['team_name']) ?></div>
                            <span class="tk-nextmatch-vs"><?=$this->escape((string)$entry['points']) ?> P</span>
                        </div>
                        <div class="tk-nextmatch-meta">
                            <div><strong><?=$this->getTrans('team') ?>:</strong> <?=$this->escape($entry['team_name']) ?></div>
                            <div><strong><?=$this->getTrans('ladder') ?>:</strong> <?=$this->escape($entry['ladder_title']) ?></div>
                            <div><strong><?=$this->getTrans('score') ?>:</strong> <?=$this->escape((string)$entry['wins']) ?>-<?=$this->escape((string)$entry['draws']) ?>-<?=$this->escape((string)$entry['losses']) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="mb-0"><?=$this->getTrans('noTopEntries') ?></p>
<?php endif; ?>
