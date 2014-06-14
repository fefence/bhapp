<?php

class LivescoreController extends \BaseController
{
    public function livescore()
    {
        return View::make('livescore');
    }
}