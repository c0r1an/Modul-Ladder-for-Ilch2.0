<?php
/** @var \Ilch\View $this */
$ladders = $this->get('ladders');
?>
<h1><?=$this->getTrans('menuAdminLadder') ?></h1>
<p><a class="btn btn-primary" href="<?=$this->getUrl(['action' => 'treat']) ?>"><?=$this->getTrans('createLadder') ?></a></p>

<?php if ($ladders): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('title') ?></th>
                <th><?=$this->getTrans('game') ?></th>
                <th><?=$this->getTrans('startAt') ?></th>
                <th><?=$this->getTrans('status') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ladders as $ladder): ?>
            <tr>
                <td><?=$this->escape((string)$ladder['id']) ?></td>
                <td>
                    <?=$this->getEditIcon(['action' => 'treat', 'id' => $ladder['id']]) ?>
                    <a href="<?=$this->getUrl(['action' => 'participants', 'id' => $ladder['id']]) ?>"><?=$this->getTrans('participants') ?></a> |
                    <a href="<?=$this->getUrl(['action' => 'matches', 'id' => $ladder['id']]) ?>"><?=$this->getTrans('matches') ?></a> |
                    <?=$this->getDeleteIcon(['action' => 'del', 'id' => $ladder['id']]) ?>
                </td>
                <td><?=$this->escape($ladder['title']) ?></td>
                <td><?=$this->escape($ladder['game']) ?></td>
                <td><?=!empty($ladder['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($ladder['start_at']))) : '-' ?></td>
                <td><span class="badge text-bg-secondary"><?=$this->getTrans($ladder['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
