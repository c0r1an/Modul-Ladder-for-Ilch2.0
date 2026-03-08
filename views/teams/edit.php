<?php
/** @var \Ilch\View $this */
$team = $this->get('team');
$members = $this->get('members') ?: [];
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team['id']]) ?>"><?=$this->getTrans('team') ?></a>
</div>

<h1><?=$this->getTrans('editTeam') ?>: <?=$this->escape($team['team_name']) ?></h1>

<form method="POST" action="" enctype="multipart/form-data" class="card rounded-0 mb-3">
    <?=$this->getTokenField() ?>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="team_name"><?=$this->getTrans('teamName') ?></label>
                <input class="form-control" type="text" id="team_name" name="team_name" value="<?=$this->escape($team['team_name']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tag"><?=$this->getTrans('tag') ?></label>
                <input class="form-control" type="text" id="tag" name="tag" maxlength="32" value="<?=$this->escape($team['tag']) ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="logo"><?=$this->getTrans('logo') ?></label>
            <input class="form-control" type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp">
        </div>
        <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
    </div>
</form>

<div class="card rounded-0 mb-3">
    <div class="card-header"><?=$this->getTrans('addMemberWithUsername') ?></div>
    <div class="card-body">
        <form method="POST" action="">
            <?=$this->getTokenField() ?>
            <div class="input-group">
                <input class="form-control" type="text" name="username" placeholder="<?=$this->getTrans('usernameOrNickname') ?>" required>
                <button class="btn btn-outline-secondary" type="submit"><?=$this->getTrans('add') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="card rounded-0">
    <div class="card-header"><?=$this->getTrans('members') ?></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th><?=$this->getTrans('nickname') ?></th>
                        <th><?=$this->getTrans('role') ?></th>
                        <th><?=$this->getTrans('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <a href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'members', 'action' => 'view', 'id' => (int)$member['id']]) ?>">
                                    <?=$this->escape($member['nickname'] ?: ('User#' . $member['user_id'])) ?>
                                </a>
                            </td>
                            <td><?=$this->escape($member['role']) ?></td>
                            <td>
                                <?php if ($member['role'] !== 'captain'): ?>
                                    <form method="POST" action="" style="display:inline-block;">
                                        <?=$this->getTokenField() ?>
                                        <input type="hidden" name="remove_member_id" value="<?=$member['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><?=$this->getTrans('remove') ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
