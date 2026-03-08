<?php

namespace Modules\Ladder\Boxes;

use Ilch\Box;
use Modules\Ladder\Mappers\MatchMapper;

class Nextladdermatches extends Box
{
    public function render()
    {
        $matchMapper = new MatchMapper();
        $this->getView()->set('matches', $matchMapper->getUpcomingForBox(5));
    }
}
