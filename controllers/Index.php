<?php

namespace Modules\Ladder\Controllers;

class Index extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $this->redirect()->to([
            'module' => 'ladder',
            'controller' => 'ladders',
            'action' => 'index',
        ]);
    }
}
