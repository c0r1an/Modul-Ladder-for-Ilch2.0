<?php
/** @var \Ilch\View $this */
$teams = $this->get('teams') ?: [];
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
</div>

<h1><?=$this->getTrans('myTeams') ?></h1>

<?php if ($teams): ?>
    <div class="row g-3">
        <?php foreach ($teams as $team): ?>
            <div class="col-12 col-lg-4">
                <div class="card h-100 rounded-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?=$this->escape($team['team_name']) ?></h5>
                        <p class="mb-1"><strong><?=$this->getTrans('tag') ?>:</strong> <?=$this->escape($team['tag'] ?: '-') ?></p>
                        <p class="mb-2"><strong><?=$this->getTrans('playersCount') ?>:</strong> <?=$this->escape((string)$team['players_count']) ?></p>
                        <p class="mb-3"><span class="badge text-bg-secondary"><?=$this->getTrans($team['status']) ?></span></p>
                        <div class="mt-auto d-flex gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['action' => 'view', 'id' => (int)$team['id']]) ?>"><?=$this->getTrans('view') ?></a>
                            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['action' => 'edit', 'id' => (int)$team['id']]) ?>"><?=$this->getTrans('edit') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>-</p>
<?php endif; ?>
