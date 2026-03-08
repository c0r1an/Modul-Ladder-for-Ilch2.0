<?php
/** @var \Ilch\View $this */
$entry = $this->get('entry');
$maxOptions = [4, 8, 16, 32, 64, 128];
$currentMax = (int)($entry['max_participants'] ?? 16);
if (!in_array($currentMax, $maxOptions, true)) {
    $currentMax = 16;
}
?>
<h1><?=($entry ? $this->getTrans('editLadder') : $this->getTrans('createLadder')) ?></h1>

<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="row">
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="title"><?=$this->getTrans('title') ?></label>
            <input class="form-control" type="text" id="title" name="title" value="<?=$this->escape($entry['title'] ?? '') ?>" required>
        </div>
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="slug"><?=$this->getTrans('slug') ?></label>
            <input class="form-control" type="text" id="slug" name="slug" value="<?=$this->escape($entry['slug'] ?? '') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-3">
            <label class="form-label" for="game"><?=$this->getTrans('game') ?></label>
            <input class="form-control" type="text" id="game" name="game" value="<?=$this->escape($entry['game'] ?? '') ?>" required>
        </div>
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="team_size"><?=$this->getTrans('teamSize') ?></label>
            <input class="form-control" type="number" min="1" id="team_size" name="team_size" value="<?=$this->escape((string)($entry['team_size'] ?? 5)) ?>" required>
        </div>
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="max_participants"><?=$this->getTrans('maxParticipants') ?></label>
            <select class="form-select" id="max_participants" name="max_participants" required>
                <?php foreach ($maxOptions as $option): ?>
                    <option value="<?=$option ?>" <?=$currentMax === $option ? 'selected' : '' ?>><?=$option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="points_win"><?=$this->getTrans('pointsWin') ?></label>
            <input class="form-control" type="number" id="points_win" name="points_win" value="<?=$this->escape((string)($entry['points_win'] ?? 3)) ?>" required>
        </div>
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="points_draw"><?=$this->getTrans('pointsDraw') ?></label>
            <input class="form-control" type="number" id="points_draw" name="points_draw" value="<?=$this->escape((string)($entry['points_draw'] ?? 1)) ?>" required>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="points_loss"><?=$this->getTrans('pointsLoss') ?></label>
            <input class="form-control" type="number" id="points_loss" name="points_loss" value="<?=$this->escape((string)($entry['points_loss'] ?? 0)) ?>" required>
        </div>
        <div class="col-xl-4 mb-3">
            <label class="form-label" for="start_at"><?=$this->getTrans('startAt') ?></label>
            <input class="form-control" type="datetime-local" id="start_at" name="start_at" value="<?=!empty($entry['start_at']) ? date('Y-m-d\TH:i', strtotime($entry['start_at'])) : '' ?>" required>
        </div>
        <div class="col-xl-4 mb-3">
            <label class="form-label" for="end_at"><?=$this->getTrans('endAt') ?></label>
            <input class="form-control" type="datetime-local" id="end_at" name="end_at" value="<?=!empty($entry['end_at']) ? date('Y-m-d\TH:i', strtotime($entry['end_at'])) : '' ?>">
        </div>
        <div class="col-xl-2 mb-3">
            <label class="form-label" for="status"><?=$this->getTrans('status') ?></label>
            <select class="form-select" id="status" name="status">
                <?php foreach (['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'] as $status): ?>
                    <option value="<?=$status ?>" <?=(!empty($entry['status']) && $entry['status'] === $status) ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="ck_1"><?=$this->getTrans('rules') ?></label>
        <textarea class="form-control ckeditor"
                  name="rules"
                  id="ck_1"
                  toolbar="ilch_html"
                  rows="8"><?=$this->escape($entry['rules'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label" for="banner"><?=$this->getTrans('banner') ?></label>
        <div class="input-group">
            <input class="form-control"
                   type="text"
                   name="banner"
                   id="selectedImage"
                   placeholder="<?=$this->getTrans('mediaPathPlaceholder') ?>"
                   value="<?=$this->escape($entry['banner'] ?? '') ?>">
            <span class="input-group-text">
                <a id="media" href="javascript:media()"><i class="fa-regular fa-image"></i></a>
            </span>
        </div>
        <?php if (!empty($entry['banner'])): ?>
            <div class="mt-2">
                <img src="<?=$this->getBaseUrl($entry['banner']) ?>" alt="<?=$this->getTrans('banner') ?>" style="max-width: 320px; height: auto;">
            </div>
        <?php endif; ?>
    </div>

    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>

<?=$this->getDialog('mediaModal', $this->getTrans('media'), '<iframe style="border:0;"></iframe>') ?>
<script>
    <?=$this->getMedia()
        ->addMediaButton($this->getUrl('admin/media/iframe/index/type/single/'))
        ->addUploadController($this->getUrl('admin/media/index/upload'))
    ?>
</script>
