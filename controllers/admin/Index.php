<?php

namespace Modules\Ladder\Controllers\Admin;

class Index extends \Ilch\Controller\Admin
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
