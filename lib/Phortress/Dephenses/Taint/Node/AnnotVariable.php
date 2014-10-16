<?php

namespace Phortress\Dephenses\Taint\Node;

/**
 * Description of AnnotVariable
 *
 * @author naomileow
 */
class AnnotVariable extends Variable{
    
    public function __construct($name, array $attributes = array(), $annot = Annotation::UNASSIGNED) {
        parent::__construct(array(
            'name' => $name,
            'annotation' => $annot
        ), $attributes);
    }

    
}
