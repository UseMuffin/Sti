<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AssistantChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator)
    {
        $validator->notEmptyString('name', 'assistant chef');

        return $validator;
    }
}
