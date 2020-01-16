<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AssistantChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator)
    {
        if (method_exists($validator, 'notEmptyString')) {
            $validator->notEmptyString('name', 'assistant chef');
        } else {
            $validator->notEmpty('name', 'assitant chef');
        }

        return $validator;
    }
}
