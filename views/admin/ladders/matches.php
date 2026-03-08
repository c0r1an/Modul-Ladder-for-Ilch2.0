<?php
/** @var \Ilch\View $this */
$ladder = $this->get('ladder');
$matches = $this->get('matches') ?: [];
$participantCount = (int)$this->get('participantCount');
?>
<h1><?=$this->escape($ladder['title']) ?> - <?=$this->getTrans('matches') ?></h1>

<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <form method="POST" action="">
        <?=$this->getTokenField() ?>
        <button class="btn btn-primary" type="submit" name="generate" value="1"><?=$this->getTrans('generateMatches') ?></button>
    </form>
    <form method="POST" action="">
        <?=$this->getTokenField() ?>
        <button class="btn btn-outline-danger" type="submit" name="reset" value="1"><?=$this->getTrans('resetMatches') ?></button>
    </form>
    <span class="text-muted"><?=$this->getTrans('acceptedParticipants') ?>: <?=$participantCount ?></span>
</div>

<?php if ($matches): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('round') ?></th>
                <th><?=$this->getTrans('match') ?></th>
                <th><?=$this->getTrans('teamOne') ?></th>
                <th><?=$this->getTrans('teamTwo') ?></th>
                <th><?=$this->getTrans('score') ?></th>
                <th><?=$this->getTrans('status') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($matches as $match): ?>
            <tr>
                <td><?=$this->escape((string)$match['id']) ?></td>
                <td>
                    <form method="POST" action="" class="d-flex flex-wrap gap-1">
                        <?=$this->getTokenField() ?>
                        <input type="hidden" name="match_id" value="<?=$match['id'] ?>">
                        <input class="form-control form-control-sm" style="width:130px;" type="text" name="map" placeholder="<?=$this->getTrans('mapPlaceholder') ?>" value="<?=$this->escape($match['map']) ?>">
                        <input class="form-control form-control-sm" style="width:90px;" type="number" min="1" name="best_of" value="<?=$this->escape((string)$match['best_of']) ?>">
                        <input class="form-control form-control-sm" style="width:180px;" type="datetime-local" name="scheduled_at" value="<?=!empty($match['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($match['scheduled_at'])) : '' ?>">
                        <input class="form-control form-control-sm" style="width:80px;" type="number" min="0" name="score1" value="<?=$match['score1'] !== null ? $this->escape((string)$match['score1']) : '' ?>" placeholder="S1">
                        <input class="form-control form-control-sm" style="width:80px;" type="number" min="0" name="score2" value="<?=$match['score2'] !== null ? $this->escape((string)$match['score2']) : '' ?>" placeholder="S2">
                        <select class="form-select form-select-sm" style="width:140px;" name="status">
                            <?php foreach (['pending','scheduled','ready','reported','dispute','done'] as $status): ?>
                                <option value="<?=$status ?>" <?=$match['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?=$this->getTrans('save') ?></button>
                        <a class="btn btn-sm btn-outline-primary" href="<?=$this->getUrl('ladder/matches/view/id/' . (int)$match['id']) ?>"><?=$this->getTrans('view') ?></a>
                    </form>
                </td>
                <td><?=$this->escape((string)$match['round']) ?></td>
                <td>#<?=$this->escape((string)$match['match_no']) ?></td>
                <td><?=$this->escape($match['participant1_tag'] ?: $match['participant1_name']) ?></td>
                <td><?=$this->escape($match['participant2_tag'] ?: $match['participant2_name']) ?></td>
                <td>
                    <?php if ($match['score1'] !== null && $match['score2'] !== null): ?>
                        <?=$this->escape((string)$match['score1']) ?> : <?=$this->escape((string)$match['score2']) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><span class="badge text-bg-secondary"><?=$this->getTrans($match['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>-</p>
<?php endif; ?>
