<?php

class SquareComposer {

	public function compose($view) {
		$data = $view->getData();
		
		$view->with('data', $data);
	
	}
}