<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AssistantChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('name', 'assistant chef');
        return $validator;
    }
}
