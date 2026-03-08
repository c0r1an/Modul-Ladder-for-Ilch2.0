<?php

namespace Modules\Ladder\Boxes;

use Ilch\Box;
use Modules\Ladder\Mappers\ParticipantMapper;

class Topladder extends Box
{
    public function render()
    {
        $mapper = new ParticipantMapper();
        $this->getView()->set('entries', $mapper->getTopForBox(5));
    }
}
