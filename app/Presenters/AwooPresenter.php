<?php


namespace App\Presenters;


class AwooPresenter extends BasePresenter
{


    public function actionGet(): void
    {
        $awoos = scandir("./static/img/randawoos");
        if ($awoos === null || !$awoos) {
            $json = array("error"=>"not_found");
        } else {
            $json = array("path" => "/static/img/randawoos/" . $awoos[array_rand($awoos)]);
        }
        $this->sendJson($json);
    }

}