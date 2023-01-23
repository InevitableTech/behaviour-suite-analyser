<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

class DisplayProcessor {
	public function display(Entities\OutcomeCollection $outcomes) {
		print_r($outcomes->getItems());
	}
}
