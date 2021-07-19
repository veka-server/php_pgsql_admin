<?php
class Requestlist extends OneFileFramework
{

    public function reqlist(){

        $content = self::getView(__DIR__.'/../view/reqlist.php', [
            'reqlist' => PGSQL::getRequests()
        ]);

        $this->show([
            'content' => $content
        ]);
    }


}