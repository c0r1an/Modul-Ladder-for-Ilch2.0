<?php
/** @var \Ilch\View $this */
$ladder = $this->get('ladder');
$participants = $this->get('participants') ?: [];
?>
<h1><?=$this->escape($ladder['title']) ?> - <?=$this->getTrans('participants') ?></h1>

<div class="card mb-3 rounded-0">
    <div class="card-header"><?=$this->getTrans('status') ?></div>
    <div class="card-body">
        <form method="POST" action="">
            <?=$this->getTokenField() ?>
            <div class="row">
                <div class="col-xl-4">
                    <select class="form-select" name="ladder_status">
                        <?php foreach (['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'] as $status): ?>
                            <option value="<?=$status ?>" <?=$ladder['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-2">
                    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($participants): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('team') ?></th>
                <th><?=$this->getTrans('tag') ?></th>
                <th><?=$this->getTrans('playersCount') ?></th>
                <th><?=$this->getTrans('captain') ?></th>
                <th><?=$this->getTrans('status') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $participant): ?>
                <tr>
                    <td><?=$this->escape((string)$participant['id']) ?></td>
                    <td>
                        <?php foreach (['accepted' => 'success', 'rejected' => 'danger', 'pending' => 'warning', 'checked_in' => 'info'] as $status => $btn): ?>
                            <form method="POST" action="" style="display:inline-block; margin-right:4px;">
                                <?=$this->getTokenField() ?>
                                <input type="hidden" name="set_status_id" value="<?=$participant['id'] ?>">
                                <input type="hidden" name="set_status" value="<?=$status ?>">
                                <button class="btn btn-sm btn-outline-<?=$btn ?>" type="submit"><?=$this->getTrans($status) ?></button>
                            </form>
                        <?php endforeach; ?>
                        <form method="POST" action="" style="display:inline-block;">
                            <?=$this->getTokenField() ?>
                            <input type="hidden" name="delete_participant_id" value="<?=$participant['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><?=$this->getTrans('delete') ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?=$this->getUrl('ladder/teams/view/id/' . (int)$participant['id']) ?>">
                            <?=$this->escape($participant['team_name']) ?>
                        </a>
                    </td>
                    <td><?=$this->escape($participant['tag'] ?: '-') ?></td>
                    <td><?=$this->escape((string)$participant['players_count']) ?></td>
                    <td><?=$this->escape($participant['captain_name'] ?: '-') ?></td>
                    <td><span class="badge text-bg-secondary"><?=$this->getTrans($participant['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>-</p>
<?php endif; ?>

<p><a class="btn btn-outline-primary" href="<?=$this->getUrl(['action' => 'matches', 'id' => $ladder['id']]) ?>"><?=$this->getTrans('matches') ?></a></p>
