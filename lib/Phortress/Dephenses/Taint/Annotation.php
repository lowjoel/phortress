<?php
namespace Phortress\Dephenses\Taint;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \MyCLabs\Enum\Enum;
/**
 * Description of newPHPClass
 *
 * @author naomileow
 */
class Annotation extends Enum{
    //put your code here
    const UNASSIGNED = 0;
    const TAINTED = 1;
    const SAFE = 2;
    const UNKNOWN = 3;
}
