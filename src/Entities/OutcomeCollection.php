<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class OutcomeCollection extends Collection {
    public function addOutcome(Outcome $item) {
        $this->add($item);
    }
}
