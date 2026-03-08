<?php
/** @var \Ilch\View $this */
$ladders = $this->get('ladders');
$selectedStatus = (string)($this->get('selectedStatus') ?? '');
$statusOptions = $this->get('statusOptions') ?: ['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'];
$indexUrl = $this->getUrl('ladder/ladders/index');
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'ladders', 'action' => 'index']) ?>"><?=$this->getTrans('menuLadder') ?></a>
    <?php if ($this->getUser()): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'ladder', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
    <?php endif; ?>
</div>

<h1><?=$this->getTrans('menuLadder') ?></h1>

<form id="ladder-status-filter-form" class="row g-2 align-items-end mb-3">
    <div class="col-sm-6 col-md-4 col-xl-3">
        <label class="form-label" for="status-filter"><?=$this->getTrans('status') ?></label>
        <select class="form-select" id="status-filter">
            <option value="<?=$this->escape($indexUrl) ?>"><?=$this->getTrans('allWithoutArchived') ?></option>
            <?php foreach ($statusOptions as $status): ?>
                <option value="<?=$this->escape($this->getUrl('ladder/ladders/index/status/' . $status)) ?>" <?=$selectedStatus === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit"><?=$this->getTrans('filter') ?></button>
    </div>
    <?php if ($selectedStatus !== ''): ?>
        <div class="col-auto">
            <a class="btn btn-outline-secondary" href="<?=$indexUrl ?>"><?=$this->getTrans('resetFilter') ?></a>
        </div>
    <?php endif; ?>
</form>

<script>
    (function () {
        var form = document.getElementById('ladder-status-filter-form');
        var select = document.getElementById('status-filter');
        if (!form || !select) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            window.location.href = select.value || '<?=$this->escape($indexUrl) ?>';
        });
    })();
</script>

<?php if ($ladders): ?>
<div class="row g-3">
    <?php foreach ($ladders as $ladder): ?>
        <div class="col-12 col-lg-4">
            <div class="card h-100 rounded-0">
                <?php if (!empty($ladder['banner'])): ?>
                    <img src="<?=$this->getBaseUrl($ladder['banner']) ?>" class="card-img-top" alt="<?=$this->escape($ladder['title']) ?>" style="height: 150px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?=$this->escape($ladder['title']) ?></h5>
                        <span class="badge text-bg-secondary ms-2"><?=$this->getTrans($ladder['status']) ?></span>
                    </div>
                    <p class="card-text mb-1"><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($ladder['game']) ?></p>
                    <p class="card-text mb-1"><strong><?=$this->getTrans('teamSize') ?>:</strong> <?=$this->escape($ladder['team_size']) ?></p>
                    <p class="card-text mb-1"><strong><?=$this->getTrans('maxParticipants') ?>:</strong> <?=$this->escape($ladder['max_participants']) ?></p>
                    <p class="card-text mb-3"><strong><?=$this->getTrans('startAt') ?>:</strong> <?=!empty($ladder['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($ladder['start_at']))) : '-' ?></p>
                    <div class="mt-auto">
                        <a class="btn btn-outline-primary btn-sm" href="<?=$this->getUrl(['action' => 'view', 'id' => $ladder['id']]) ?>"><?=$this->getTrans('overview') ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p><?=$this->getTrans('noLadders') ?></p>
<?php endif; ?>
